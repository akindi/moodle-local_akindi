Akindi's Moodle Plugin
======================

Synchronize course rosters and grades between `Akindi`__ and Moodle (2.7+, 3.0+).

__ https://akindi.com


Installation
============

1. `Contact Akindi`__ to get API keys for your Moodle instance. You will receive
   two key pairs, one for development and one for production. They will look
   something like this::

    Testing:
    - Secret: sk_test_1234
    - Public: pk_test_abcd
    
    Production:
    - Secret: sk_abc123
    - Public: pk_xyz789

__ mailto:support@akindi.com

2. Download the latest version of the plugin from Github:
   https://github.com/akindi/moodle-local_akindi/archive/master.zip

3. Login to your Moodle instance, and under the Administration panel, expand
   *Plugins* and click *Install plugins*:

.. image:: https://raw.githubusercontent.com/akindi/moodle-local_akindi/master/doc-img/install-plugin-menu.png

4. Under the *Install plugin from ZIP file* heading, click the *Choose a file…*
   button:

.. image:: https://raw.githubusercontent.com/akindi/moodle-local_akindi/master/doc-img/install-pick-zip.png

5. Click *Choose File*, select the zip file downloaded in step 2, then click *Upload this file*:

.. image:: https://raw.githubusercontent.com/akindi/moodle-local_akindi/master/doc-img/install-upload-zip.png

6. Once the zip file has been uploaded it should appear in the file list below
   the *Install plugin from ZIP file* heading. Click the *Install plugin from
   the ZIP file* button:

.. image:: https://raw.githubusercontent.com/akindi/moodle-local_akindi/master/doc-img/install-from-zip-file.png

7. Once the plugin has been validated, click *Continue*:

.. image:: https://raw.githubusercontent.com/akindi/moodle-local_akindi/master/doc-img/install-continue.png

8. Under the Administration panel, expand *Plugins*, then *Local plugins*, and
   click *Akindi Settings*. Fill in the values appropriate to your
   installation:

   ``akindi_launch_url``
       | Testing: ``https://dev.akindi.com/api/moodle/launch``
       | Production: ``https://akindi.com/api/moodle/launch``

   ``akindi_public_key``
       The key given to you by Akindi in step 1 (it will start with ``pk_``).

   ``akindi_secret_key``
       The key given to you by Akindi in step 1 (it will start with ``sk_``).

   ``akindi_instance_secret``
       A secret key you have generated which *should not* be shared with
       Akindi. The default value is generated randomly on each page load and
       is a suitable default.

       This key is used to sign tokens sent to Akindi and should not be
       changed after the initial application setup.

.. image:: https://raw.githubusercontent.com/akindi/moodle-local_akindi/master/doc-img/install-configure-plugin.png

9. Test your integration: navigate to a course, expand *Course
   administration*, then click *Launch Akindi*!

Usage Notes
===========

Akindi assumes that student's ``idnumber`` field will be a numeric student ID.
Akindi will still function if it isn't, but the instructor will have to manually
assign each scanned sheet to a student.
