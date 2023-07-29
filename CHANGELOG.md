# 1.3.0
- **Alternative registration possibility for using Simplyment loaders in custom extension** \
Instead of calling the loaders in multiple files, only one registration in your Configuration/Services.yaml is needed now.
Example for registration of an extension:
    ```yaml
    services:
      MyVendorName\MyExtensionKey:
        tags:
          - name: simplyment
    ```
    
    *Note: The previous registration option through calling the loaders in the files 
ext_localconf.php, ext_tables.php, Configuration/Extbase/Persistence/Classes.php or Configuration/TCA/Overrides/tt_content.php 
is still available!*
\
\
    Many thanks to Benni Mack for the inspiration of this solution!