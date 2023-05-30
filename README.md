# ATC-Booking
 This project pulls current bookings from the VATSIM Germany API and generates an image to be displayed in the Forum etc.


## Contact

<!-- The naming convention is as follows:
**Option A:** First name + surname
**Option B:** First name + first letter of surname + Vatsim ID -->

|         Name         | Responsible for |      Contact       |
| :------------------: | :-------------: | :----------------: |
| Nikolas G. - 1373921 |       \*        | `git[at]vatger.de` |
|  Paul H. - 1450775   |       \*        | `git[at]vatger.de` |

## Prerequisites
- **PHP 8.x**
  - with common php modules including gd


## Running the Application
1. Install any PHP webserver (XAMPP, Nginx + PHP FPM / CGI, Apache ...) 
2. Copy `conf.php.example` to `conf.php` and edit its contents.
3. Point the main directory to `/public` and start your webserver.
