#!/bin/sh

# export PATH=/usr/local/bin:/usr/sbin:$PATH
export PATH=/usr/local/bin:/usr/sbin:/home/faxocr/bin/:$PATH

# get configuration

CONF_FILE=~faxocr/etc/faxocr.conf
CONF_PROC=~faxocr/bin/doconfig.sh

. $CONF_FILE
. $CONF_PROC

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
SHEET_COUNT=0

if [ "`ls $MDIR`" = '' ]; then
    exit
fi

mkdir $MBACKDIR 2> /dev/null
mkdir $FBACKDIR 2> /dev/null
mkdir $LOGDIR 2> /dev/null
mkdir $UNTMPDIR 2> /dev/null

ruby rails/script/getsrml.rb > $SHEETREADERCONF/srml/faxocr.xml
for MFILE in `ls $MDIR`
do
	#
	# directory setting / preprocessing
	#
	echo FOUND: $MDIR/$MFILE
	echo FOUND: $MDIR/$MFILE >> $LOG
	echo BACKUP MAIL: $MBACKDIR/$MFILE
	echo BACKUP MAIL: $MBACKDIR/$MFILE >> $LOG
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
	SRHMODE="faximo"
	if [ "$ISFAXIMO" != "" ]; then
		SRHMODE="faximo"
	fi
	if [ "$ISMESSAGEPLUS" != "" ]; then
		SRHMODE="messageplus"
	fi
	FFROM=`srhelper -m from -s $SRHMODE $MDIR/$MFILE`
	if [ "$FFROM" = "" ]; then
		FFROM="UNNUMBER"
	fi
	FTO=`srhelper -m to -s $SRHMODE $MDIR/$MFILE`
	if [ "$FTO" = "" ]; then
		FTO="UNNUMBER"
	fi
	echo FAX: from:$FFROM to:$FTO
	echo FAX: from:$FFROM to:$FTO >> $LOG

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
		convert $ATTACHED_TIFF $UNTMPDIR/single%d.tif
		mv $UNTMPDIR/single%d.tif $UNTMPDIR/single.tif 
	fi

	# XXX
	# pwd >> /tmp/taka-log
	# echo $ATTACHED_TIFF >> /tmp/taka-log
	# cp $ATTACHED_TIFF /tmp/taka-tiff-orig.tif
	# cp $UNTMPDIR/single* /tmp

	for TIFFILE in `ls $UNTMPDIR/single*`
	do
		#
		# Sheetreader processing
		#
		#echo BACKUP TIF: $MBACKDIR"/"$FFROM"_"$FTO"_"$DATE"_"$TIME.TIF
		#echo BACKUP TIF: $MBACKDIR"/"$FFROM"_"$FTO"_"$DATE"_"$TIME.TIF >> $LOG
		SHEET_COUNT=`expr $SHEET_COUNT + 1`
		BACKTIFF=$FBACKDIR"/"$FFROM"_"$FTO"_"$DATE"_"$TIME"_"$SHEET_COUNT.TIF
		echo BACKUP TIF: $BACKTIFF
		echo BACKUP TIF: $BACKTIFF >> $LOG

		convert -resample 200 $TIFFILE $BACKTIFF
		sheetreader -m rails -c $SHEETREADERCONF $OCR_DIR -r $FTO -s $FFROM -p $ANALYZEDIR \
		    $BACKTIFF 2>> $LOG 1> $FBACKDIR"/"$FFROM"_"$FTO"_"$DATE"_"$TIME"_"$SHEET_COUNT".rb"
		SRRESULT=$?
		echo SHEETREADER: $SRRESULT
		echo SHEETREADER: $SRRESULT >> $LOG

		#
		# Error file processing
		#
		if [ "$FFROM" != "UNNUMBER" -a "$SRRESULT" != "0" ]; then
		    echo SEND ERROR MAIL
		    sendfax $FFROM errorreport $ERRORPDF
		fi

		#
		# Echo file processing
		#
		ruby $FBACKDIR"/"$FFROM"_"$FTO"_"$DATE"_"$TIME"_"$SHEET_COUNT".rb" $RAILSPATH $ANALYZEDIR \
		    $ECHOFILE
		RUBYRESULT=$?
		if [ "$RUBYRESULT" = "1" ]; then
		    echo SEND ECHO MAIL
		    sendfax $FFROM echoreport $ECHOFILE.pdf
		    rm $ECHOFILE.pdf
		    rm $ECHOFILE.html
		fi
	done
	rm $UNTMPDIR/* 2>> $LOG
done
rmdir $UNTMPDIR 2>> $LOG
