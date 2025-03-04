This application/API runs on the user’s server (in my case, a NAS), where scanned documents are automatically stored. 
The application requires read and write access to the scan folder.

The application is executed via a cron job. 
It takes files from the scan folder and sends them, in this case, to the “Thrömer Portal,” which automatically stores the files in the DMS.

The files are then deleted from the NAS.
