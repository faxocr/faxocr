show_error_message()
{
	_header=`date +%Y/%m/%d\ %H:%M:%S`

	echo $_header Error: "$@" >&2
}

show_info_message()
{
	_header=`date +%Y/%m/%d\ %H:%M:%S`

	echo $_header Info: "$@"
}

show_message_to_console()
{
	_header=`date +%Y/%m/%d\ %H:%M:%S`

	echo $_header Info: "$@"
	echo $_header Info: "$@" >&2
}

#
# args: cmd_exit_status
#	message
#
show_cmd_result()
{
	_header=`date +%Y/%m/%d\ %H:%M:%S`
	_cmd_exit_status=$1
	shift

	if [ "$_cmd_exit_status" = 0 ]; then
		show_info_message "$@" ": success"
	else
		show_info_message "$@" ": failed"
	fi
}

show_info_message_and_logfile()
{
	show_info_message "$@"
	show_info_message "$@" >> $LOG_FILE_FOR_THIS_SESSION
}

show_cmd_result_and_logfile()
{
	show_cmd_result "$@"
	show_cmd_result "$@" >> $LOG_FILE_FOR_THIS_SESSION
}
