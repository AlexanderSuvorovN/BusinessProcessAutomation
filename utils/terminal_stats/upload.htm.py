# to parse html file instead: https://zetcode.com/python/beautifulsoup/
# -*- coding: utf-8 -*-
from bs4 import BeautifulSoup
import mysql.connector
import re
from datetime import datetime
import os
from pathlib import Path
from os import listdir
from os.path import isfile, join
# https://stackoverflow.com/questions/6996603/how-to-delete-a-file-or-folder
import shutil
import socket

# https://stackoverflow.com/questions/4271740/how-can-i-use-python-to-get-the-system-hostname
hostname = socket.gethostname().strip()

config = {}
config['verbose'] = False
config['quiet'] = True
if hostname == 'us01hv':
    config['server'] = 'business.smart-teams.ru'
    config['reports_path'] = r'/home/ftpuser/mipko/XRDP'
else:
    config['server'] = 'localhost'
    config['reports_path'] = r'./mipko/XRDP'

if config['quiet'] == False:
    print()
    print("Connecting to database...")
try:
    cnx = mysql.connector.connect(
        # host='business.smart-teams.ru',
        host=config['server'],
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
dirlist = listdir(config['reports_path'])
files_processed = 0
for e in dirlist:
    if not isfile(e):
        terminal_username = e.strip()
        files = Path(config['reports_path']+'/'+e).glob('*.htm')
        files_count = 0
        for file in files:
            if config['quiet'] == False:
                print(file)
            records_processed = 0
            records_inserted = 0           
            with open(file, 'r', encoding='utf-8-sig') as f:
                html = f.read()
                soup = BeautifulSoup(html, 'html.parser')
                for table in soup.find_all('table'):
                    classname = table.get('class')
                    if classname == None or (not 'head' in classname and not 'head1' in classname):
                        record_html = {}
                        for tr in table.find_all('tr'):
                            cells = tr.find_all('td')
                            cells_count = len(cells)
                            if(cells_count == 1):
                                continue
                            if(cells_count == 3):
                                ix_name = 1
                                ix_value = 2
                            if(cells_count == 2):
                                ix_name = 0
                                ix_value = 1
                            # https://stackoverflow.com/questions/15478127/remove-final-character-from-string
                            field_name = cells[ix_name].b.string.strip()[:-1]
                            field_value = ''
                            for tag in cells[ix_value].find_all('br'):
                                tag.replaceWith(';')
                            for string in cells[ix_value].strings:
                                field_value += string.strip()
                            if field_name == 'Дата':
                                pattern = '^([0-9]{2})\.([0-9]{2})\.([0-9]{4})\s([0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2})$'
                                # https://pythex.org/
                                x = re.search(pattern, field_value)
                                date_day = x.group(1)
                                date_month = x.group(2)
                                date_year = x.group(3)
                                date_time = x.group(4)        
                                field_value = f'{date_year}-{date_month}-{date_day} {date_time}'
                            record_html[field_name] = field_value
                        if config['verbose'] == True:
                            for field_name in record_html:
                                print('[html]['+field_name+']: '+record_html[field_name])
                        record_db = {}
                        record_db['terminal_username'] = terminal_username
                        record_db['date'] = record_html['Дата']
                        record_db['type'] = None
                        record_db['application_path'] = None
                        record_db['application_name'] = None
                        record_db['application_window_title'] = None
                        record_db['details'] = None
                        if 'Активность пользователя' in record_html:
                            record_db['type'] = 'Активность пользователя'
                            record_db['details'] = record_html['Активность пользователя']
                        if 'Активность программ' in record_html:
                            record_db['type'] = 'Активность программ'
                            pattern = '^([0-9a-zA-Zа-яА-Я_\-\s\.()\/]+)\s\-\s([a-zA-Z]\:\\\\.+\\\\[0-9a-zA-Zа-яА-Я_\-\s\.()]+)$'
                            x = re.search(pattern, record_html['Программа'])
                            record_db['application_name'] = x.group(1)
                            record_db['application_path'] = x.group(2)
                            # record_db['details'] = record_html['Активность программ']
                        if 'Посещенные веб-сайты' in record_html:
                            record_db['type'] = 'Посещенные веб-сайты'
                            pattern = '^([0-9a-zA-Zа-яА-Я_\-\s\.()\/]+)\s\-\s([a-zA-Z]\:\\\\.+\\\\[0-9a-zA-Zа-яА-Я_\-\s\.()]+)$'
                            x = re.search(pattern, record_html['Программа'])
                            record_db['application_name'] = x.group(1)
                            record_db['application_path'] = x.group(2)
                            if 'Заголовок окна' in record_html:
                                record_db['application_window_title'] = record_html['Заголовок окна']
                            record_db['details'] = record_html['Посещенные веб-сайты']
                        if config['verbose'] == True:
                            for field_name in record_db:
                                print('[db]['+field_name+']: '+str(record_db[field_name]))
                            print()
                        if record_db['type'] != None:
                            query = "INSERT INTO `terminal_stats` (`terminal_username`, `date`, `type`, `application_path`, `application_name`, `application_window_title`, `details`) VALUES (%(terminal_username)s, %(date)s, %(type)s, %(application_path)s, %(application_name)s, %(application_window_title)s, %(details)s)"
                            cursor.execute(query, record_db)
                            records_inserted += 1
                        records_processed += 1
            if config['quiet'] == False:
                print(f'\tRecords processed: {records_processed}')
                print(f'\tRecords inserted: {records_inserted}')
                print()
            cnx.commit()
            os.remove(file)
            # https://stackoverflow.com/questions/52825134/convert-windowspath-to-string
            pattern = '^(.+)\.htm$'
            x = re.search(pattern, str(file))
            if x != None:
                dirname = x.group(1)+'_files'
                if os.path.isdir(dirname):
                    shutil.rmtree(dirname)
            # datetime.now().strftime('%Y-%m-%d %H:%M:%S')
            print(f'[{datetime.now()}] - Successfully processed "{file}"')
            files_processed += 1
            files_count += 1
        if files_count == 0:
            print(f'[{datetime.now()}] - No files to process for terminal user "{terminal_username}"')
            continue
cnx.close()
if config['quiet'] == False:
    print('Complete.')
    print()