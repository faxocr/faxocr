#!/bin/sh -x
export PATH=/usr/local/bin:/usr/sbin:$PATH

RAILSPATH="./faxocr"
SHEETREADERCONF="./faxocr/faxocr_config/receive_sheetreader"

# FAX service
FAXADDR="@xxxxx.xx"
FROMADDR="xxxx@xxxxx.xx"
# User and Password for the NTT-Com iFax
FAXUSER="xxxxxxxx"
FAXPASS="xxxxxxxx"

ERRORMAIL="/tmp/error"$$".eml"
ECHOFILE="/tmp/echofile"$$
ECHOMAIL="/tmp/echofile"$$".eml"
DATE=`date +%Y%m%d`
TIME=`date +%H%M%S`
MDIR="./Maildir/new"
ANALYZEDIR="/home/faxocr/Faxsystem/analyzedimage/"
UNTMPDIR="./Faxsystem/Tempmunpack"
MBACKDIR="./Faxsystem/Mailbackup/"$DATE
FBACKDIR="./Faxsystem/Faxbackup/"$DATE
ERRORPDF="./Faxsystem/FaxMessages/error.pdf"
LOGDIR="./Faxsystem/Log/"$DATE
LOG=$LOGDIR"/procfax.log"

mkdir $MBACKDIR 2> /dev/null
mkdir $FBACKDIR 2> /dev/null
mkdir $LOGDIR 2> /dev/null

ruby faxocr/script/getsrml.rb > $SHEETREADERCONF/srml/faxocr.xml
for MFILE in `ls $MDIR`
do
	echo FOUND: $MDIR/$MFILE
	echo FOUND: $MDIR/$MFILE >> $LOG
	# backup mail data
	echo BACKUP MAIL: $MBACKDIR/$MFILE
	echo BACKUP MAIL: $MBACKDIR/$MFILE >> $LOG
	cp $MDIR/$MFILE $MBACKDIR
	ISFAXIMO=`grep "faximo.jp" $MDIR/$MFILE | head -1`
	ISMESSAGEPLUS=`grep "everynet.jp" $MDIR/$MFILE | head -1`
	SRHMODE="faximo"
	if [ "$ISFAXIMO" != "" ]
	then
		SRHMODE="faximo"
	fi
	if [ "$ISMESSAGEPLUS" != "" ]
	then
		SRHMODE="messageplus"
	fi
	FFROM=`srhelper -m from -s $SRHMODE $MDIR/$MFILE`
	if [ "$FFROM" = "" ]
	then
		FFROM="UNNUMBER"
	fi
	FTO=`srhelper -m to -s $SRHMODE $MDIR/$MFILE`
	if [ "$FTO" = "" ]
	then
		FTO="UNNUMBER"
	fi
 	cat $MDIR/$MFILE | munpack -C $UNTMPDIR 2>> $LOG 1>> $LOG
	TIFFILE=`ls $UNTMPDIR/* | grep -ie TIF$ 2>> $LOG |head -1`
	echo FAX: from:$FFROM to:$FTO
	echo FAX: from:$FFROM to:$FTO >> $LOG
	if [ "$TIFFILE" != "" ]
	then
		echo BACKUP TIF: $MBACKDIR"/"$FFROM"_"$FTO"_"$DATE"_"$TIME.TIF
		echo BACKUP TIF: $MBACKDIR"/"$FFROM"_"$FTO"_"$DATE"_"$TIME.TIF >> $LOG
		BACKTIFF=$FBACKDIR"/"$FFROM"_"$FTO"_"$DATE"_"$TIME.TIF
		convert -resample 200 $TIFFILE $BACKTIFF
		sheetreader -m rails -c $SHEETREADERCONF -r $FTO -s $FFROM -p $ANALYZEDIR $BACKTIFF 2>> $LOG 1> $FBACKDIR"/"$FFROM"_"$FTO"_"$DATE"_"$TIME".rb"
		SRRESULT=$?
		echo SHEETREADER: $SRRESULT
		echo SHEETREADER: $SRRESULT >> $LOG
		if [ "$FFROM" != "UNNUMBER" ]
		then
		if [ "$SRRESULT" != "0" ]
		then
			echo "#userid="$FAXUSER > $ERRORMAIL.pass
			echo "#passwd="$FAXPASS >> $ERRORMAIL.pass
			echo SEND ERROR MAIL
			sendemail -t $FFROM$FAXADDR -u errorreport -a $ERRORPDF -o message-file=$ERRORMAIL.pass -f $FROMADDR
			rm $ERRORMAIL.pass
			cat $FBACKDIR"/"$FFROM"_"$FTO"_"$DATE"_"$TIME".rb" 
		fi
		fi
		ruby $FBACKDIR"/"$FFROM"_"$FTO"_"$DATE"_"$TIME".rb" $RAILSPATH $ANALYZEDIR $ECHOFILE
		RUBYRESULT=$? 
		if [ "$RUBYRESULT" = "1" ]
		then
			echo "#userid="$FAXUSER > $ECHOMAIL.pass
			echo "#passwd="$FAXPASS >> $ECHOMAIL.pass
			echo SEND ECHO MAIL
			sendemail -t $FFROM$FAXADDR -u echoreport -a $ECHOFILE.pdf -o message-file=$ECHOMAIL.pass -f $FROMADDR
			rm $ECHOMAIL.pass
			rm $ECHOFILE.pdf
			rm $ECHOFILE.html
		fi
			rm $UNTMPDIR/* 2>> $LOG
        fi
rm  $MDIR/$MFILE
done

