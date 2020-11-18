#!/bin/sh

script_dir=`dirname "$0"`
FAXOCR_ROOT=${FAXOCR_ROOT:="`(cd "$script_dir" && cd .. && pwd)`"}

export PATH=/usr/local/bin:/usr/sbin:$PATH

# get configuration

CONF_FILE=${FAXOCR_ROOT}/etc/faxocr.conf
CONF_PROC=${FAXOCR_ROOT}/bin/doconfig.sh
UTIL_FILE=${FAXOCR_ROOT}/bin/procfax_utils.sh

. $CONF_FILE
. $CONF_PROC
. $UTIL_FILE

SURVEYID=$1
REPYEAR=$2
REPMONTH=$3
REPDAY=$4
SENDNUMBER=$5

REPHTML="/tmp/procrep"$$".html"
REPMAIL="/tmp/procrep"$$".eml"
LOG=$SESSION_LOG_DIR"/procrep.log"

mkdir $SEND_BACKUP_DIR 2> /dev/null

RAILS_ENV=${RAILS_ENV:="undefined"}
if [ x"$RAILS_ENV" = x"undefined" ]; then
	rails_current_running_mode=`get_current_running_mode_of_rails`
	export RAILS_ENV=${rails_current_running_mode:="production"}
fi

#
# generate the report and send it (NEEDS CHECK)
#
$RAILS_ROOT_DIR/script/send_daily_report.rb $DATE $TIME $SHEETREADER_ANALYZE_DIR $SEND_BACKUP_DIR \
    $FAX_SEND_FROM $FAX_SEND_TARGET

if [ "`ls $SEND_BACKUP_DIR`" = '' ]; then
    rm -r $SEND_BACKUP_DIR
fi

