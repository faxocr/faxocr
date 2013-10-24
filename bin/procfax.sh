#!/bin/sh

# export PATH=/usr/local/bin:/usr/sbin:$PATH
export PATH=/usr/local/bin:/usr/sbin:/home/faxocr/bin/:$PATH

# get configuration

CONF_FILE=~faxocr/etc/faxocr.conf
CONF_PROC=~faxocr/bin/doconfig.sh

. $CONF_FILE
. $CONF_PROC

show_error_message()
{
	_header=`date +%Y/%m/%d\ %H:%M:%S`

	echo $_header Error: "$@"
	echo $_header Error: "$@" >> $LOG
	echo $_header Error: "$@" >&2
}

show_info_message()
{
	_header=`date +%Y/%m/%d\ %H:%M:%S`

	echo $_header Info: "$@"
	echo $_header Info: "$@" >> $LOG
}

show_message_to_console()
{
	_header=`date +%Y/%m/%d\ %H:%M:%S`

	echo $_header Info: "$@"
	echo $_header Info: "$@" >&2
}

# move to home directory
cd ~faxocr

# cannot run multiple instance
LOCKFILE=${DIR_FAX}"/"`basename $0`.lock
trap 'echo "trapped."; rm -f ${LOCKFILE}; exit 1' 1 2 3 15

if ! ln -s $$ ${LOCKFILE}; then
    show_message_to_console 'Cannot run multiple instances.'
    exit 1
fi

# receive fax
if [ "$FAX_RECV_SETTING" = "pop3" ]; then
	getfax
fi

# temp files
ERRORMAIL="/tmp/error"$$".eml"
ECHOFILE="/tmp/echofile"$$
ECHOMAIL="/tmp/echofile"$$".eml"
LOG=$LOGDIR"/procfax.log"
TIME=`date +%H%M%S`
FAX_COUNT=0
FAX_ERROR_COUNT=0
SHEET_COUNT=0
SHEET_ERROR_COUNT=0

if [ "`ls $MDIR`" = '' ]; then
    echo `date +%Y/%m/%d\ %H:%M:%S` "NOT FOUND: not found new email"
    rm ${LOCKFILE}
    exit
fi

mkdir $MBACKDIR 2> /dev/null
mkdir $FBACKDIR 2> /dev/null
mkdir $LOGDIR 2> /dev/null
mkdir $UNTMPDIR 2> /dev/null

ruby rails/script/getsrml.rb > $SHEETREADERCONF/srml/faxocr.xml
for MFILE in `ls $MDIR`
do
	# initialize local variable
	FAX_ERROR_HAPPENS_FLAG=0

	FAX_COUNT=`expr $FAX_COUNT + 1`

	#
	# directory setting / preprocessing
	#
	show_info_message FOUND: $MDIR/$MFILE
	show_info_message BACKUP MAIL: $MBACKDIR/$MFILE
	cp $MDIR/$MFILE $MBACKDIR

	# removes messages from root
	ISFROMROOT=`grep "From: " $MDIR/$MFILE | grep root | head -1`
	if [ "$ISFROMROOT" != "" ]; then
		rm $MDIR/$MFILE
		continue;
	fi

	#
	# recognize from/to number (based on fax service type)
	#
	ISFAXIMO=`grep "faximo.jp" $MDIR/$MFILE | head -1`
	ISMESSAGEPLUS=`grep "everynet.jp" $MDIR/$MFILE | head -1`
	ISBIZFAX=`grep "050fax.jp" $MDIR/$MFILE | head -1`
	SRHMODE="faximo"
	if [ "$ISFAXIMO" != "" ]; then
		SRHMODE="faximo"
	fi
	if [ "$ISMESSAGEPLUS" != "" ]; then
		SRHMODE="messageplus"
	fi
	if [ "$ISBIZFAX" != "" ]; then
		SRHMODE="bizfax"
	fi
	if [ x"$ISFAXIMO" = x"" -a x"$ISMESSAGEPLUS" = x"" -a x"$ISBIZFAX" = x"" ]; then
		show_error_message FAX: ERROR: cannot recognize a fax service from Mail
		FAX_ERROR_HAPPENS_FLAG=1
	fi
	FFROM=`srhelper -m from -s $SRHMODE $MDIR/$MFILE`
	if [ "$FFROM" = "" ]; then
		FFROM="UNNUMBER"
	fi
	FTO=`srhelper -m to -s $SRHMODE $MDIR/$MFILE`
	if [ "$FTO" = "" ]; then
		FTO="UNNUMBER"
	fi
	show_info_message FAX: from:$FFROM to:$FTO


	#
	# unpack the fax image file
	#
 	cat $MDIR/$MFILE | munpack -C $UNTMPDIR 2>> $LOG 1>> $LOG
	rm $MDIR/$MFILE

	UNTMPDIR_FILES=`ls $UNTMPDIR/* | wc -l`
	if [ "$UNTMPDIR_FILES" -gt "0" ]; then
		ATTACHED_TIFF=`ls $UNTMPDIR/* | grep -ie TIF$ 2>> $LOG |head -1`
	fi
	if [ "$ATTACHED_TIFF" != "" ]; then
		# When a tiff file has only one page, old version of converter
		# command generates "single%d.tif" instead of "single0.tif".
		# On the other hand a newer version of converter command
		# generates "single0.tif".
		convert $ATTACHED_TIFF $UNTMPDIR/single%d.tif
		if [ -e $UNTMPDIR/single%d.tif ]; then
			mv $UNTMPDIR/single%d.tif $UNTMPDIR/single.tif
		fi
	fi

	# XXX
	# pwd >> /tmp/taka-log
	# echo $ATTACHED_TIFF >> /tmp/taka-log
	# cp $ATTACHED_TIFF /tmp/taka-tiff-orig.tif
	# cp $UNTMPDIR/single* /tmp

	for TIFFILE in `ls $UNTMPDIR/single*`
	do
		# initialize local variable
		SHEET_ERROR_HAPPENS_FLAG=0

		#
		# Sheetreader processing
		#
		#echo BACKUP TIF: $MBACKDIR"/"$FFROM"_"$FTO"_"$DATE"_"$TIME.TIF
		#echo BACKUP TIF: $MBACKDIR"/"$FFROM"_"$FTO"_"$DATE"_"$TIME.TIF >> $LOG
		SHEET_COUNT=`expr $SHEET_COUNT + 1`
		BACKTIFF=$FBACKDIR"/"$FFROM"_"$FTO"_"$DATE"_"$TIME"_"$SHEET_COUNT.TIF
		show_info_message BACKUP TIF: $BACKTIFF

		convert -resample 200 $TIFFILE $BACKTIFF
		sheetreader -m rails -c $SHEETREADERCONF $OCR_DIR -r $FTO -s $FFROM -p $ANALYZEDIR \
			$BACKTIFF 2>> $LOG 1> $FBACKDIR"/"$FFROM"_"$FTO"_"$DATE"_"$TIME"_"$SHEET_COUNT".rb"
		SRRESULT=$?
		if [ $SRRESULT -ne 0 ]; then
			show_error_message SHEETREADER: ERROR: sheetreader returns non-zero value: $SRRESULT
			SHEET_ERROR_HAPPENS_FLAG=1
		else
			show_info_message SHEETREADER: $SRRESULT
		fi

		#
		# generate thumbnail image
		#
		SRDATE=`grep answer_sheet.date $FBACKDIR"/"$FFROM"_"$FTO"_"$DATE"_"$TIME"_"$SHEET_COUNT".rb" | head -1 | cut -d\" -f2`
		if [ x"${SRDATE}" = x"" ]; then
			show_error_message SHEETREADER: date used in sheetreader: ERROR: result is empty
			SHEET_ERROR_HAPPENS_FLAG=1
		else
			show_info_message SHEETREADER: date used in sheetreader: $SRDATE
		fi
		IMAGEDIR=${ANALYZEDIR}R${FFROM}/S${FTO}/${SRDATE}
		convert -geometry 500 ${IMAGEDIR}/image.png ${IMAGEDIR}/image_thumb.png

		#
		# Error file processing
		#
		if [ "$FFROM" != "UNNUMBER" -a "$SRRESULT" != "0" ]; then
			show_info_message SEND ERROR MAIL
			sendfax $FFROM errorreport $ERRORPDF
		fi

		#
		# Echo file processing
		#
		ruby $FBACKDIR"/"$FFROM"_"$FTO"_"$DATE"_"$TIME"_"$SHEET_COUNT".rb" $RAILSPATH $ANALYZEDIR \
			$ECHOFILE
		RUBYRESULT=$?
		if [ "$RUBYRESULT" = "8" ]; then
			show_info_message SEND ECHO MAIL
			sendfax $FFROM echoreport $ECHOFILE.pdf
			rm $ECHOFILE.pdf
			rm $ECHOFILE.html
		fi
		if [ $SHEET_ERROR_HAPPENS_FLAG -eq 1 ]; then
			SHEET_ERROR_COUNT=`expr $SHEET_ERROR_COUNT + 1`
			FAX_ERROR_HAPPENS_FLAG=1
		fi
	done
	rm $UNTMPDIR/* 2>> $LOG
	if [ $FAX_ERROR_HAPPENS_FLAG -eq 1 ]; then
		FAX_ERROR_COUNT=`expr $FAX_ERROR_COUNT + 1`
	fi
done
show_info_message "INFO: Number of fax processed:$FAX_COUNT"
show_info_message "INFO: Number of sheet processed:$SHEET_COUNT"
show_info_message "INFO: Number of error occured processing fax:$FAX_ERROR_COUNT"
show_info_message "INFO: Number of error occured processing sheet:$SHEET_ERROR_COUNT"
rmdir $UNTMPDIR 2>> $LOG

rm ${LOCKFILE}
