#!/bin/bash
# @copyright  GPL License 2010 - Vikas Mahajan - http://vikasmahajan.wordpress.com
# @copyright  GPL License 2013 - Florian HEnry - florian.henry@open-concept.pro
# @copyright  GPL License 2017 - Laurent Destailleur - eldy@users.sourceforge.net
# @copyright  GPL License 2019 - Camille Lafitte - cam.lafit@azerttyu.net 
#
# Convert an ODT into a PDF using "native" or "jodconverter" or "pyodconverter" or "unoconv" tool.
# PowerERP variable MAIN_ODT_AS_PDF must be defined ...
#  to value "libreoffice" to call soffice native exporter feature (in such a case, this script is useless)  
#  or value "unoconv" to call unoconv CLI tool after ODT generation.
#  or value "pyodconverter" to call DocumentConverter.py after ODT generation.
#  or value "jodconverter" to call jodconverter wrapper after ODT generation
#  or value "/pathto/jodconverter-cli-file.jar" to call jodconverter java tool without wrapper after ODT generation.
# PowerERP variable MAIN_DOL_SCRIPTS_ROOT must be defined to path of script directories (otherwise powererp will try to guess).
#
# NOTE: Using this script is depcrecated, you can now convert generated ODT to PDF on the fly by setting the value MAIN_ODT_AS_PDF
# to 'libreoffice'. It requires only soffice (OpenOffice or LibreOffice) installed on server (use apt install soffice libreoffice-common libreoffice-writer).
# If you got this error: javaldx failed! Warning: failed to read path from javaldx with no return to prompt when running soffice --headless -env:UserInstallation=file:"/tmp" --convert-to pdf --outdir xxx ./yyy.odt, 
# check that directory defined into env:UserInstallation parameters exists and is writeable.


if [ "x$1" == "x" ] 
then
	echo "Usage:   odt2pdf.sh fullfilename [native|unoconv|jodconverter|pyodconverter|pathtojodconverterjar]"
	echo "Example: odt2pdf.sh myfile unoconv"
	echo "Example: odt2pdf.sh myfile ~/jodconverter/jodconverter-cli-2.2.2.jar"
	exit
fi




# Full patch where soffice is installed 
soffice="/usr/bin/soffice"

# Temporary directory (web user must have permission to read/write). You can set here path to your DOL_DATA_ROOT/admin/temp directory for example. 
home_java="/tmp"


# Main program
if [ -f "$1.odt" ]
then

  if [ "x$2" == "xnative" ]
  then
      $soffice --headless -env:UserInstallation=file:///$home_java/ --convert-to  pdf:writer_pdf_Export --outdir $(dirname $1) "$1.odt"
      exit 0
  fi

  if [ "x$2" == "xunoconv" ]
  then
      # See issue https://github.com/dagwieers/unoconv/issues/87
      /usr/bin/unoconv -vvv "$1.odt"
      retcode=$?
	  if [ $retcode -ne 0 ]
	   then
	    echo "Error while converting odt to pdf: $retcode"
	    exit 1
	  fi
	  exit 0
  fi

  nbprocess=$(pgrep -c soffice)
  if [ $nbprocess -ne 1 ]	# If there is some soffice process running
   then
    cmd="$soffice --invisible --accept=socket,host=127.0.0.1,port=8100;urp; --nofirststartwizard --headless -env:UserInstallation=file:///$home_java/"
    export HOME=$home_java && cd $home_java && $cmd&
    retcode=$?
    if [ $retcode -ne 0 ]
     then
      echo "Error running soffice: $retcode"
      exit 1
    fi
    sleep 2
  fi
  
  if [ "x$2" == "xjodconverter" ]
  then
      jodconverter "$1.odt" "$1.pdf"
  else
      if [ "x$2" == "xpyodconverter" ]
      then
         python DocumentConverter.py "$1.odt" "$1.pdf"
      else
         java -jar $2 "$1.odt" "$1.pdf" 
      fi
  fi
  
  retcode=$?
  if [ $retcode -ne 0 ]
   then
    echo "Error while converting odt to pdf: $retcode"
    exit 1
  fi
  
  sleep 1
else
  echo "Error: Odt file $1.odt does not exist"
  exit 1
fi
