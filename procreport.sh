#!/bin/sh
export PATH=/usr/local/bin:/usr/sbin:$PATH

RAILSPATH="./faxocr"
SURVEYID=$1
REPYEAR=$2
REPMONTH=$3
REPDAY=$4
SENDNUMBER=$5

FAXADDR="@xxxxxxx.xx"
FAXUSER="xxxxxxxx"
FAXPASS="xxxxxxxx"
FROMADDR="xxxxxx@xxxxxx.xx"
REPHTML="/tmp/procrep"$$".html"
REPMAIL="/tmp/procrep"$$".eml"
DATE=`date +%Y%m%d%H%M`
ANALYZEDIR="/home/faxocr/Faxsystem/analyzedimage"
UNTMPDIR="./Faxsystem/Tempmunpack"
MBACKDIR="./Faxsystem/Mailbackup/"$DATE
SBACKDIR="./Faxsystem/Sendbackup/"$DATE
LOGDIR="./Faxsystem/Log/"$DATE
LOG=$LOGDIR"/procrep.log"

mkdir $SBACKDIR 2> /dev/null

$RAILSPATH/script/send_daily_report.rb $DATE $ANALYZEDIR $SBACKDIR $FROMADDR $FAXADDR $FAXUSER $FAXPASS

