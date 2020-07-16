# pdfmerger
Merge pdf files including password protected files using pdftk and iLovepdf

1. Clone this project to var/html folder
2. Create account on iLovepdf (https://developer.ilovepdf.com/)
3. Replace $public_key = 'project_public_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'; in function.php
4. Install pdftk http://www.angusj.com/pdftkb/
5. Check installation path and replace exec('/usr/local/bin/pdftk *.pdf cat output bigmergedfile.pdf'); in index.php if required
6. Add pdf files to folder and run project.
