sogotoics.php
This is a relatively simple command line export from SOGo to individual ics and vcf files

For each user in a user list, configurable within the script, 3 files are extracted from the SOGo database:

	user_cal.ics  - Calendar data
	user_task.ics - Task list data
	user.vcf      - User personal contact data

Since SOGo saves in these formats natively, we are able to simply read the field contents and output the data directly.

Tested only in SOGO 1.3.16
