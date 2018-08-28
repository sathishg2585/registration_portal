CONTENTS OF THIS FILE
---------------------
 * Introduction
 * Requirements
 * Installation
 * Configuration

INTRODUCTION
------------
This module allows users to authenticate against a SAML identity provider
to login to your Drupal site. After adding the configuration your Drupal site
can be used as a SAML service provider.

Read more about SAML on wikipedia: https://en.wikipedia.org/wiki/SAML_2.0

REQUIREMENTS
------------
This module depends on OneLogin's SAML PHP Toolkit:
https://github.com/onelogin/php-saml

DEMO
------------
Watch a detailed explanation on how to use this module (v1) in the video
tutorial: https://www.youtube.com/watch?v=7XCp0SvFoPQ

INSTALLATION
------------
Install as you would normally install a contributed drupal module. See:
https://www.drupal.org/documentation/install/modules-themes/modules-8
for further information.

CONFIGURATION
-------------
Create a public/private key pair to use Drupal as a service provider.
openssl req -new -x509 -days 3652 -nodes -out sp.crt -keyout sp.key

Go to /admin/config/people/saml to configure the module.

Service Provider Configuration:
Entity ID:        [choose a unique name]
Name ID:          urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified
x509 Certificate: [the generated public key]
Private Key:      [the generated private key]

Your cert/key pair can be set in the admin screen, or kept on disk - in which
case you need to enter only the path. (The SAML Toolkit puts some restrictions
on the directory name, though.)

Ask for the metadata XML from the identity provider. Retrieve the needed
settings below from the metadata XML.

Identity Provider Configuration:
- Entity ID
- Single Sign On Service
- Single Log Out Service
- Change Password Service
- x509 Certificate

Supply the generated metadata XML to identity provider to get the service
provider added. Everything the identity provider needs is in the metadata XML.
  /saml/metadata

Add permissions for metadata XML to the anonymous user if it should be
anonymously accessible to the identity provider.

This should be enough to do a basic login. Configure the module to create new
users if needed or allow it to map existing users. The specific configuration
depends on the attributes delivered by the identity provider.
