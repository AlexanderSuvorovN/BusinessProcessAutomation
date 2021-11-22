# parse csv file: https://realpython.com/python-csv/
# to parse html file instead: https://zetcode.com/python/beautifulsoup/
import csv
import mysql.connector
import re
from datetime import datetime
import os
from pathlib import Path

config = {}
config['verbose'] = False
config['quiet'] = True
config['reports_path'] = r'./reports'
config['errors_path'] = r'./errors'

if config['quiet'] == False:
    print()
    print("Connecting to database...")
try:
    cnx = mysql.connector.connect(
        # host='business.smart-teams.ru',
        host='localhost',
        database='smartteams_business',
        user='smartteams_business',
        passwd='cj6iajw6oKRmq7S7')
except mysql.connector.Error as err:
    if err.errno == errorcode.ER_ACCESS_DENIED_ERROR:
        print("Something is wrong with your user name or password")
    elif err.errno == errorcode.ER_BAD_DB_ERROR:
        print("Database does not exist")
    else:
        print(err)
cursor = cnx.cursor(dictionary=True)

# https://stackoverflow.com/questions/39909655/listing-of-all-files-in-directory
p = Path(config['reports_path']).glob('*.csv')
# files = [x for x in p if x.is_file()]
files_processed = 0
for x in p:
    if x.is_file():
        filename = x
        # https://stackoverflow.com/questions/678236/how-to-get-the-filename-without-the-extension-from-a-path-in-python
        basename = os.path.basename(filename)
        pattern = '^([a-z]+)\s\([0-9]{2}\-[0-9]{2}\-[0-9]{4}\s\-\s[0-9]{2}\-[0-9]{2}\-[0-9]{4}\)\s\-\s\([0-9]+\s\-\s([0-9]+)\)\.csv$'
        # https://pythex.org/
        x = re.search(pattern, basename)
        terminal_username = str(x.group(1))
        records_count = int(x.group(2))
        if config['quiet'] == False:
            print()
            print(f'Reading terminal statistics file "{filename}"')
        # https://stackoverflow.com/questions/17912307/u-ufeff-in-python-string
        with open(filename, 'r', encoding='utf-8-sig') as csv_file:
            csv_reader = csv.reader(csv_file, delimiter=';')
            # csv_reader = csv.DictReader(csv_file, delimiter=';')
            if config['quiet'] == False:
                print('Parsing CSV file...')
            records_processed = 0
            min_fields = None
            max_fields = None
            for row in csv_reader:
                fields_count = len(row)
                if min_fields == None or fields_count < min_fields:
                    min_fields = fields_count
                if max_fields == None or fields_count > max_fields:
                    max_fields = fields_count
                record = {}
                record['terminal_username'] = terminal_username
                record['date'] = row[0]        
                record['type'] = row[1]
                record['application_path'] = row[2]
                record['application_name'] = row[3]
                record['application_window_title'] = row[4]
                record['details'] = row[5]
                pattern = '^([0-9]{2})\.([0-9]{2})\.([0-9]{4})\s([0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2})$'
                # https://pythex.org/
                x = re.search(pattern, record['date'])
                date_day = x.group(1)
                date_month = x.group(2)
                date_year = x.group(3)
                date_time = x.group(4)        
                record['date'] = f'{date_year}-{date_month}-{date_day} {date_time}'
                if config['quiet'] == False and config['verbose'] == True:
                    print()
                    print(row)
                    print(f'terminal_username: {record["terminal_username"]}')
                    print(f'date: {record["date"]}')
                    print(f'type: {record["type"]}')
                    print(f'application path: {record["application_path"]}')
                    print(f'applicaton name: {record["application_name"]}')
                    print(f'application window title: {record["application_window_title"]}')
                    print(f'details: {record["details"]}')
                    print(f'fields in record: {fields_count}')        
                query = "INSERT INTO `terminal_stats` (`terminal_username`, `date`, `type`, `application_path`, `application_name`, `application_window_title`, `details`) VALUES (%(terminal_username)s, %(date)s, %(type)s, %(application_path)s, %(application_name)s, %(application_window_title)s, %(details)s)"
                cursor.execute(query, record)
                if config['quiet'] == False:
                    print(f'Inserted: {row}')
                records_processed += 1
            if config['quiet'] == False:
                print(f'Processed {records_processed} lines. Min fields: {min_fields}. Max fields: {max_fields}.')
        cnx.commit()
        cnx.close()
        if records_processed == records_count:
            os.remove(filename)
            # datetime.now().strftime('%Y-%m-%d %H:%M:%S')
            print(f'[{datetime.now()}] - Successfully processed "{filename}"')
        else:
            print(f'[{datetime.now()}] - Error while processing "{filename}"')
            if not os.path.exists(config['errors_path']):
                os.makedirs(config['errors_path'])
            # https://stackoverflow.com/questions/8858008/how-to-move-a-file
            os.rename(filename, config['errors_path']+'/'+filename)
        files_processed += 1
if files_processed == 0:
    print(f'[{datetime.now()}] - No .csv files found in "{config["reports_path"]}" directory.')
if config['quiet'] == False:
    print('Complete.')
    print()