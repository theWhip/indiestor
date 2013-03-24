Indiestor collaborative video sharing for Avid
==============================================

Debian package build and publication instructions.

1. Deploy locally
-----------------
For test purposes, you can rapidly deploy a new version locally, without building a package.
Use the `sys-indiestor-local-test-deploy.sh` script to deploy locally.
Use the `sys-indiestor-local-test-remove.sh` script to undeploy locally.

2. Commit changes to git
------------------------
        $ ./sys-git-push-commit.sh "my commit message"

3. Flag the new package version in git
--------------------------------------
        $ ./sys-git-push-tag.sh x.y.z.t

4. Build the package
--------------------
        $ ./sys-package-build.sh x.y.z.t

It will create the following files in ../native:

        $ ls ..
        indiestor_0.8.0.10_all.deb        indiestor_0.8.0.10.dsc     native
        indiestor_0.8.0.10_amd64.changes  indiestor_0.8.0.10.tar.gz

These are the deployment packages:

        .deb: binary installer
        .dsc: manifest
        .changes: change file
        .tar.gz: source distribution for the package

You can undo the package build and delete all package files produced with the script `sys-package-clean.sh`:

        $ ./sys-package-clean.sh

Note that the file `sys-package-copy-files.sh` is used only by the Debian `rules` file and not meant to be started directly.

5. Publish the package
----------------------
The repository for the distribution and its version must already have been created. If the corresponding folders and files do not yet exist, the script will fail. Execute the publication script `sys-package-publish.sh`:

        $ ./sys-package-publish.sh x.y.z.t

6. All in one go
-----------------

        $ ./sys-release.sh x.y.z.t

For example, version is 0.9.0.1. `sys-release.sh` will execute:

        $ echo 0.9.0.1 > VERSION.txt
        $ ./sys-git-push-commit.sh 0.9.0.1
        $ ./sys-git-push-tag.sh 0.9.0.1
        $ ./sys-package-build.sh 0.9.0.1
        $ ./sys-package-publish.sh 0.9.0.1

7. Install the package
----------------------
On a third user machine, the user can install the package by executing/following the procedure in `sys-user-install-indiestor.sh`. It is an example procedure for ubuntu/precise.

