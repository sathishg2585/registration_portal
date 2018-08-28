CONTENTS OF THIS FILE
---------------------
   
 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Maintainers


INTRODUCTION
------------

Webform Node Element allows you to display content from your site as an element within a webform.

* For a full description of the module, visit the project page:
   https://drupal.org/project/webform_node_element

* To submit bug reports and feature suggestions, or to track changes:
   https://drupal.org/project/issues/webform_node_element


REQUIREMENTS
------------

This module requires the following modules:

 * Webform (https://drupal.org/project/webform)


INSTALLATION
------------
 
 * Install as you would normally install a contributed Drupal module. See:
   https://drupal.org/documentation/install/modules-themes/modules-8
   for further information


CONFIGURATION
-------------

When enabled this module provides a new type of element, 'Node', to be added
to a webform. Once you add an element of type 'Node' you can enter the nid
of the node that you want to display. When you view the webform the node will
be displayed using the webform_element display mode.

To dynamically set the nid or the display mode, an event is dispatched prior
to rendering the element. Subscribe to the 

    WebformNodeElementPreRender::PRERENDER

event, and call the 

    setNid
    setDisplayMode

methods. See the webform_node_element_example module for a working example..


MAINTAINERS
-----------

Current maintainers:
 * Andrew Larcombe (alarcombe) - https://drupal.org/u/alarcombe
