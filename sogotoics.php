#!/usr/bin/php
<?php
/* sogotoics.php - Miles Lott <mlott@gie.com>
 * (c)2013 Gulf Interstate Engineering, Co.
 *
 * Exports data from SOGo database to ics files for calendar and tasks
 * and vcf files for contacts.  SOGo saves this content in these formats,
 * so no data conversion is done here.
 *
 * These files are usable for import to another collaboration system.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * v 0.1 12/13/2013
*/

	/* One possible source for the user list is the location of a previous backup made by sogo-tool */
	//$userlist = `/bin/ls /backup/sogo`;

	/* Or, simply enter a list of names separated by \n */
	//$userlist = "bob\njoan\ndave";

	/* Or, read a file containing a list of names, one per line */
	//$userlist = @file_get_contents('userlist.txt');

	if(!@$userlist)
	{
		echo "Please open the file and setup a source for your user list\n";
		echo "There are no command line options for this program...\n";
		exit;
	}

	/* Show some debug comments and contents inline */
	$debug = False;
	/* Configure your database server host, username, password, and db */
	$db = mysqli_connect('localhost','sogo','sogo','sogo');

	/* No further configuration - files will be created in the current directory. */

	$users = explode("\n",$userlist);

	foreach($users as $user)
	{
		$res = $db->query("SHOW tables FROM sogo LIKE 'sogo${user}%'");
		/* SOGo uses 4 tables per user.  One for calendar and tasks, one for contacts, one for acl and one cache.
		 * We only care about the first two here.  The names of the tables are, e.g., sogobob012390123 and not
		 * identifiable as to content.  So, we search both for all types of data, returning only content we want
		 * for each type of data.
		 */

		while($row = mysqli_fetch_array($res))
		{
			$table = trim($row[0]);
			if(
				!preg_match('/Tables_in_sogo/',$table) &&
				!preg_match('/acl$/',$table) &&
				!preg_match('/quick$/',$table) &&
				!preg_match('/%$/',$table)
			)
			{
				if($debug)
				{
					echo "Working on table: $table\n";
				}

				/* Create the user_cal.ics Calendar events file */
				@unlink($user, '_cal.ics');
				$ics = fopen($user . '_cal.ics', "a");
				$res2 = $db->query('SELECT c_content FROM ' . $table . " WHERE c_content LIKE '%VEVENT%'");
				while($row2 = mysqli_fetch_array($res2))
				{
					$data = $row2['c_content'];
					fwrite($ics, $data . "\n");
					if($debug)
					{
						echo $data . "\n";
					}
				}
				fclose($ics);

				/* Create the user_task.ics Tasks file */
				@unlink($user, '_task.ics');
				$task = fopen($user . '_task.ics', "a");
				$res2 = $db->query('SELECT c_content FROM ' . $table . " WHERE c_content LIKE '%VTODO%'");
				while($row2 = mysqli_fetch_array($res2))
				{
					$data = $row2['c_content'];
					fwrite($task, $data . "\n");
					if($debug)
					{
						echo $data . "\n";
					}
				}
				fclose($task);

				/* Create the user.vcf Contacts file */
				@unlink($user, '.vcf');
				$vcf = fopen($user . '.vcf', "a");
				$res2 = $db->query('SELECT c_content FROM ' . $table . " WHERE c_content LIKE '%VCARD%'");
				while($row2 = mysqli_fetch_array($res2))
				{
					$data = $row2['c_content'];
					fwrite($vcf, $data . "\n");
					if($debug)
					{
						echo $data . "\n";
					}
				}
				fclose($vcf);
			}
		}
	}
