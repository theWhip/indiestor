Indiestor collaborative video sharing for Avid
==============================================

Debian package build and publication instructions.

1. Manage repositories
----------------------
A distribution in our context is an apt-get-based gnu/linux distribution, usually derived directly or indirectly from debian.
Examples are: Ubuntu, Debian, Mint, and so on.
We currently create binary installation packages for apt-get-based systems only.
Each distribution has distribution releases. For example, for Ubuntu, you will find oneiric, precise, quantal, and so on.
Inscribe the distribution/releases for which we distribute in the file `config-reprepro.txt`.
An example reprepro configuration could be:
        ubuntu/oneiric
        ubuntu/precise
        ubuntu/quantal
        debian/squeeze

You can update configuration of the distribution machine in the file `config-machine.sh` (see below).

Run the script `sys-reprepro-fix.sh` to apply the new distribution and releases configuration. The script will create new distribution and release folders or delete them where applicable, and modify existing distribution configuration files, based on the templates that you can find the folder `reprepro-templates`. You will still need to publish the packages to this repository structure (see below).

2. Applicable configuration
---------------------------
All build and publication scripts retrieve their information from `config-default.sh`.
Example:

        #!/usr/bin/env bash
        source ./config-machine.sh
        distribution=ubuntu
        distrib_version=precise
        package_version=0.8.0.10
        architecture=amd64

This configuration is a copy of `config-ubuntu-precise.sh`. When building for another distribution and version,
create the file `config-distrib-vers.sh` and copy it over `config-default.sh`.

The `config-machine.sh` script contains the following entries:

        package=indiestor
        domain=packages.indiestor.com
        user_machine=packages@$domain
        user_home_remote=/home/packages
        user_repository_root=$user_home_remote/$domain/html/apt

It is the root level configuration file.

3. Deploy locally
-----------------
For test purposes, you can rapidly deploy a new version locally, without building a package.
Use the `sys-indiestor-local-test-deploy.sh` script to deploy locally.
Use the `sys-indiestor-local-test-remove.sh` script to undeploy locally.

4. Flag the new package version in git
--------------------------------------
Before building, make sure that `config-default.sh` is the appropriate architecture that you want to build for.
Before building, make sure to flag the current version in git with the new package version number.
Use the script `sys-git-push-version.php`. It takes two arguments: `version` and `commit message`:

        $ ./sys-git-push-version.php 1.1.0.4 'fixed issues 3420, 3421, and 3422'

The script will update the version in `config-default.sh`. Copy back the file to its unique name:

        $ cp config-default.sh config-ubuntu-precise.sh
        $ git add -A . ; git commit -m 'changed version'; git push origin

The current package version is now inscribed both in the git repository and in the build process.

5. Build the package
--------------------
Execute the build script `sys-package-build.sh`:

        $ ./sys-package-build.sh

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

6. Publish the package
----------------------
The repository for the distribution and its version must already have been created. If the corresponding folders and files do not yet exist, the script will fail. Execute the publication script `sys-package-publish.sh`:

        $ ./sys-package-publish.sh

You can undo the publication with the script `sys-package-unpublish.sh`: 

        $ ./sys-package-unpublish.sh

You cannot publish exactly the same distribution/version/package/version twice, without removing such publication first. The checksums will not match and the publication will fail. The script file `sys-package-publish.remote.sh` will be automatically copied to the publication server and executed there. It is not meant to be started directly.

7. Install the package
----------------------
On a third user machine, the user can install the package by executing/following the procedure in `sys-user-install-indiestor.sh`. It is an example procedure for ubuntu/precise.

