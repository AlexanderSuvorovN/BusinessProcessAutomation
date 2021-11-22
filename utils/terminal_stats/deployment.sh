#!/bin/bash
ftp_dir = '/home/ftpuser/smartteams'
st_dir = '/opt/smartteams/terminals_statistics'
filename = 'upload.htm.py'
mv $ftp_dir/$filename $st_dir/
chmod +x $st_dir/$filename