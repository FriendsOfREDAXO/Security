# FOR Security for REDAXO CMS >5.15

AddOn which provides a simple way to secure your REDAXO backend with different methods.

* Frontend Password for staging or dev systems
* Error Reporting via E-Mail. Directly or as Packages
* IP Access Control: IPs and IP-Ranges for blocking and allowing frontend and backend 
* BackendSession Config: Session lifetime, KeepAlivePing, MaxSessionDuration
* Backend User Log: Log all backend user actions
* Header security: Strict-Transport-security, X-Frame-Options, X-XSS-Protection .. (in process)
* Checklist: Check your REDAXO installation for security issues with external tools.

## Install

* Use REDAXO Installer as soon as a release is available
* or clone this repository into your REDAXO installation

## Documentation

### Frontend Password

You can activate the overall frontend password in the REDAXO backend under `System > security > Frontend Password`.

#### Console

* Overview/Info: bin/console security:fe_access -i
* Help: bin/console security:fe_access --help
* Activate: bin/console security:fe_access -s 1
* Deactivate: bin/console security:fe_access -s 0

### IP Access Control

Especially for staging or dev systems it is useful to block the access to the frontend and backend for all IPs except the ones you need. You can activate the IP Access Control in the REDAXO backend under `System > security > IP Access Control`. 
The backend access should also be blocked for only the IPs you need. 

#### Console

* Overview/Info: bin/console security:ip_access -l
* Help: bin/console security:ip_access --help
* Add IP: bin/console security:ip_access -a
* Delete IP: bin/console security:ip_access -d

### Backend-User-Log

Is found in the REDAXO Backend under `System > Log > Backend User Log` and shows all backend user actions if activated.

### Error Reporting (Mail)

Error Reporting can be used instantly on every Error with an E-Mail or as an E-Mail with a summary of all errors in a specific time period.
