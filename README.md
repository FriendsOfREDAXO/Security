# Securit for REDAXO CMS >5.15

AddOn which provides a simple way to secure your REDAXO backend with different methods.

* Frontend Password for staging or dev systems
* Error Reporting via E-Mail. Directly or as Packages
* IP Access Control: IPs and IP-Ranges for blocking and allowing frontend and backend 
* BackendSession Config: Session lifetime, KeepAlivePing, MaxSessionDuration
* Backend User Log: Log all backend user actions
* Header Security: Strict-Transport-Security, X-Frame-Options, X-XSS-Protection .. (in process)
* Checklist: Check your REDAXO installation for security issues with external tools.

## Install

* Use REDAXO Installer as soon as a release is available
* or clone this repository into your REDAXO installation

## Documentation

### Frontend Password

#### Console

* Overview/Info: bin/console securit:fe_access -i
* Help: bin/console securit:fe_access --help
* Activate: bin/console securit:fe_access -s 1
* Deactivate: bin/console securit:fe_access -s 0

### IP Access Control

#### Console

* Overview/Info: bin/console securit:ip_access -l
* Help: bin/console securit:ip_access --help
* Add IP: bin/console securit:ip_access -a
* Delete IP: bin/console securit:ip_access -d
