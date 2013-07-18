#!/bin/sh

# REMOVE THIS
#
# export PATH=/usr/local/bin:/usr/sbin:$PATH
# echo ${#SERVER_TYPE}
# SERVER_TYPE="aws"

if [ -z "$SERVER_TYPE" ]; then
    SERVER_TYPE="default"
fi

if [ "$OCR_ENGINE" = "kocr" ]; then
    OCR_DIR="-l ~faxocr/etc/"
fi

#
# AWS specific processing
#
if [ "$SERVER_TYPE" = "aws" -a ! -z "$AWS_ADDRESS" ]; then
    EC2USERDATA=/tmp/ec2userdata$$
    wget -O $EC2USERDATA http://$AWS_ADDRESS/latest/user-data/ >/dev/null 2>&1
    . $EC2USERDATA
    rm $EC2USERDATA

    # for compatibility
    if [ ! -z "$FAXFROMADDR" ]; then
	FAX_SEND_FROM=$FAXFROMADDR
    fi

    if [ ! -z "$FAXFROMADDR" ]; then
	FAX_SEND_TARGET=$FAXTODOMAIN
    fi

    POP3_HOST=$POP3HOST
    POP3_PORT=$POP3PORT
    POP3_USER=$POP3USER
    POP3_PASSWORD=$POP3PASSWORD
    POP3_SSL=$POP3SSL

    SMTP_SSL=$SMTPSSL
    SMTP_HOST=$SMTPHOST
    SMTP_PORT=$SMTPPORT
    SMTP_USER=$SMTPUSER
    SMTP_PASSWORD=$SMTPPASSWORD
    SMTP_FROM=$SMTPFROM
    SMTP_AUTH=$SMTPAUTH

    FAX_USER=$FAXUSER
    FAX_PASS=$FAXPASS
fi

#
# for some critical parameters
#
if [ -z "$SMTP_SSL" ] ; then
    SMTP_SSL="no"
fi

if [ "$SMTP_SSL" = "true" ] ; then
    SMTP_SSL="yes"
else
    SMTP_SSL="no"
fi

if [ -z "$SMTP_HOST" ] ; then
    SMTP_HOST="no"
fi


#################
# DO NOT EDIT
#

# Directory setting (No need to edit)
DIR_RAILS="./rails"
DIR_MAIL="./Maildir/new"
DIR_FAX="./Faxsystem"

DATE=`date +%Y%m%d%H%M`
MDIR=$DIR_MAIL

RAILSPATH=$DIR_RAILS
SHEETREADERCONF=$DIR_RAILS"/faxocr_config/receive_sheetreader"
ANALYZEDIR=$DIR_FAX"/analyzedimage/"

UNTMPDIR=$DIR_FAX"/Tempmunpack/"`date +%H%M%S`
MBACKDIR=$DIR_FAX"/Mailbackup/"$DATE
SBACKDIR=$DIR_FAX"/Sendbackup/"$DATE
MBACKDIR=$DIR_FAX"/Mailbackup/"$DATE
FBACKDIR=$DIR_FAX"/Faxbackup/"$DATE
LOGDIR=$DIR_FAX"/Log/"$DATE
