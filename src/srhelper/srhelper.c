/*
 * srhelper.c
 *
 *  Created on: Feb 2, 2010
 *      Author: kentaro
 */

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <signal.h>
#include <unistd.h>
#include <sys/types.h>
#include <regex.h>
#include "srhelper.h"

int main(int argc, char *argv[])
{
	int debug;
	int help = 0;
	int optval;
	int result = 0;
	FILE *fp = NULL;
	char cstrbuff[1024];
	char tstrbuff[1024];
	regex_t reg;
	int reg_cmp = 0;
	regex_t reg_target;
	int reg_target_cmp = 0;
	regmatch_t pmatch[1];
	int status;
	char *service_cstr = NULL;
	char *mode_cstr = NULL;
	int searching_data = 1;
	SRHELPER_SERVICE service = SRHELPER_SERVICE_FAXIMO;
	SRHELPER_MODE mode = SRHELPER_MODE_FROM;
	char *number_tag = "[0-9][1-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]";
	char *messageplus_tag = "_[0-9][1-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9].tif";

	do {
		/* Checks options. */
		while ((optval = getopt(argc, argv, "s:m:c:v?")) != -1) {
			switch (optval) {
			case 's':
				service_cstr = optarg;
				break;
			case 'm':
				mode_cstr = optarg;
				break;
			case '?':
			case 'v':
				help = 1;
				break;
			}
			optarg = NULL;
		}
		if (argc < 2 || optind != (argc - 1) || help != 0)
		{
			result = -1;
			break;
		}
	    /* */
		if (service_cstr != NULL) {
			if (strcmp(service_cstr, "faximo") == 0)
			{
			    regcomp(&reg, number_tag, REG_EXTENDED);
			    reg_cmp = 1;
				service = SRHELPER_SERVICE_FAXIMO;
			}
			else if (strcmp(service_cstr, "ifax") == 0)
			{
			    regcomp(&reg, number_tag, REG_EXTENDED);
			    reg_cmp = 1;
				service = SRHELPER_SERVICE_IFAX;
			}
			else if (strcmp(service_cstr, "messageplus") == 0)
			{
			    regcomp(&reg, messageplus_tag, REG_EXTENDED);
			    reg_cmp = 1;
			    regcomp(&reg_target, number_tag, REG_EXTENDED);
			    reg_target_cmp = 1;
				service = SRHELPER_SERVICE_MESSAGEPLUS;
			}
			else if (strcmp(service_cstr, "telcl") == 0)
			{
			    regcomp(&reg, number_tag, REG_EXTENDED);
			    reg_cmp = 1;
				service = SRHELPER_SERVICE_MKI_TELCL;
			}
		} else {
			break;
		}
	    /* */
		if (mode_cstr != NULL) {
			if (strcmp(mode_cstr, "from") == 0)
			{
				mode = SRHELPER_MODE_FROM;
			}
			else if (strcmp(mode_cstr, "to") == 0)
			{
				mode = SRHELPER_MODE_TO;
			}
		}
	    /* */
		fp = fopen(argv[optind], "r");
		while (fgets(cstrbuff, sizeof(cstrbuff), fp) && searching_data == 1)
		{
			for (status =0 ; status < strlen(cstrbuff); status ++)
			{
				if (cstrbuff[status] == '\r' || cstrbuff[status] == '\n')
				{
					cstrbuff[status] = 0;
					break;
				}
			}
			//printf("%s\n", cstrbuff);
		    status = regexec(&reg, cstrbuff, 1, pmatch, 0);
		    if (status != 0) continue;
		    /* */
			//printf("so:%d eo:%d len:%d\n", pmatch[0].rm_so, pmatch[0].rm_eo, strlen(cstrbuff));
		    switch(service)
		    {
		    case SRHELPER_SERVICE_FAXIMO:
		    	if (mode == SRHELPER_MODE_FROM) {
					if (strlen(cstrbuff) == 42) {
						cstrbuff[pmatch[0].rm_eo] = 0;
						printf("%s", cstrbuff + pmatch[0].rm_so);
						searching_data = 0;
					}
		    	} else {
					if (strlen(cstrbuff) == 78) {
						cstrbuff[pmatch[0].rm_eo] = 0;
						printf("%s", cstrbuff + pmatch[0].rm_so);
						searching_data = 0;
					}
		    	}
		    	break;
		    case SRHELPER_SERVICE_MESSAGEPLUS:
		    	if (mode == SRHELPER_MODE_FROM) {
					cstrbuff[pmatch[0].rm_eo] = 0;
		    		strncpy(tstrbuff, cstrbuff + pmatch[0].rm_so, sizeof(tstrbuff));
				    status = regexec(&reg_target, tstrbuff, 1, pmatch, 0);
				    if (status == 0) {
				    	tstrbuff[pmatch[0].rm_eo] = 0;
						printf("%s", tstrbuff + pmatch[0].rm_so);
						searching_data = 0;
					}
		    	} else {
					searching_data = 0;
		    	}
		    	break;
		    default:
		    	break;
		    }
		}
		fclose(fp);
	} while (0);
	if (reg_cmp) {
		regfree(&reg);
	}
	if (reg_target_cmp) {
		regfree(&reg_target);
	}
	return 0;
}
