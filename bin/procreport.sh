#!/bin/sh

export PATH=/usr/local/bin:/usr/sbin:$PATH

# get configuration

CONF_FILE=~faxocr/etc/faxocr.conf
CONF_PROC=~faxocr/bin/doconfig.sh

. $CONF_FILE
. $CONF_PROC

SURVEYID=$1
REPYEAR=$2
REPMONTH=$3
REPDAY=$4
SENDNUMBER=$5

REPHTML="/tmp/procrep"$$".html"
REPMAIL="/tmp/procrep"$$".eml"
LOG=$LOGDIR"/procrep.log"

mkdir $SBACKDIR 2> /dev/null

#
# generate the report and send it (NEEDS CHECK)
#
(cd $RAILSPATH; bundle exec ./script/send_daily_report.rb $DATE $ANALYZEDIR $SBACKDIR \
	$FAX_SEND_FROM $FAX_SEND_TARGET)

if [ "`ls $SBACKDIR`" = '' ]; then
    rm -r $SBACKDIR
fi

