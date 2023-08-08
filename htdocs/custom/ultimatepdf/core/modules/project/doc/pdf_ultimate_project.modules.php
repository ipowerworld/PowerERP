<?php
/* Copyright (C) 2010      Regis Houssin  <regis.houssin@capnetworks.com>
 * Copyright (C) 2011-2020 Philippe Grand <philippe.grand@atoo-net.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * or see http://www.gnu.org/
 */

/**
 *	\file       htdocs/custom/ultimatepdf/core/modules/project/pdf/pdf_ultimate_project.modules.php
 *	\ingroup    project
 *	\brief      File of class to generate project document ultimate_project
 */

require_once DOL_DOCUMENT_ROOT.'/core/modules/project/modules_project.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
dol_include_once("/ultimatepdf/lib/ultimatepdf.lib.php");


/**
 * Class to manage generation of project document ultimate_project
 */

class pdf_ultimate_project extends ModelePDFProjects
{
	/**
     * @var DoliDb Database handler
     */
    public $db;
	
	/**
     * @var string model name
     */
    public $name;
	
	/**
     * @var string model description (short text)
     */
    public $description;
	
	/**
     * @var string document type
     */
    public $type;
	
	/**
     * @var array() Minimum version of PHP required by module.
	 * e.g.: PHP ≥ 5.5 = array(5, 5)
     */
	public $phpmin = array(5, 5); 
	
	/**
     * PowerERP version of the loaded document
     * @public string
     */
	public $version = 'PowerERP';

    /**
     * @var int page_largeur
     */
    public $page_largeur;
	
	/**
     * @var int page_hauteur
     */
    public $page_hauteur;
	
	/**
     * @var array format
     */
    public $format;
	
	/**
     * @var int marge_gauche
     */
	public $marge_gauche;
	
	/**
     * @var int marge_droite
     */
	public $marge_droite;
	
	/**
     * @var int marge_haute
     */
	public $marge_haute;
	
	/**
     * @var int marge_basse
     */
	public $marge_basse;
	
	/**
     * @var array style
     */
	public $style;
	
	/**
     * @var string logo_height
     */
	public $logo_height;

	/**
	* Issuer
	* @var Societe
	*/
	public $emetteur;

	/**
	 *	Constructor
	 *
	 *  @param		DoliDB		$DB      Database handler
	 */
	public function __construct($db)
	{
		global $conf, $langs, $mysoc;
		
		// Translations
		$langs->loadLangs(array("main", "projects", "companies", "ultimatepdf@ultimatepdf"));

		$this->db = $db;
		$this->name = "ultimate project";
		$this->description = $langs->trans("DocumentModelBaleine");

		// Dimension page pour format A4
		$this->type = 'pdf';
		$formatarray=pdf_getFormat();
		$this->page_largeur = $formatarray['width'];
		$this->page_hauteur = $formatarray['height'];
		$this->format = array($this->page_largeur,$this->page_hauteur);
		$this->marge_gauche=isset($conf->global->ULTIMATE_PDF_MARGIN_LEFT)?$conf->global->ULTIMATE_PDF_MARGIN_LEFT:10;
		$this->marge_droite=isset($conf->global->ULTIMATE_PDF_MARGIN_RIGHT)?$conf->global->ULTIMATE_PDF_MARGIN_RIGHT:10;
		$this->marge_haute =isset($conf->global->ULTIMATE_PDF_MARGIN_TOP)?$conf->global->ULTIMATE_PDF_MARGIN_TOP:10;
		$this->marge_basse =isset($conf->global->ULTIMATE_PDF_MARGIN_BOTTOM)?$conf->global->ULTIMATE_PDF_MARGIN_BOTTOM:10;

		$this->option_logo = 1;                    // Affiche logo FAC_PDF_LOGO
		$this->option_tva = 1;                     // Gere option tva FACTURE_TVAOPTION
		$this->option_codeproduitservice = 1;      // Affiche code produit-service

		$bordercolor = array('0','63','127');
		$roundradius = isset($conf->global->ULTIMATE_SET_RADIUS)?$conf->global->ULTIMATE_SET_RADIUS:2;
		$dashdotted = isset($conf->global->ULTIMATE_DASH_DOTTED)?$conf->global->ULTIMATE_DASH_DOTTED:'';
		if(! empty($conf->global->ULTIMATE_BORDERCOLOR_COLOR))
		{
			$bordercolor = html2rgb($conf->global->ULTIMATE_BORDERCOLOR_COLOR);
			if(! empty($conf->global->ULTIMATE_DASH_DOTTED))
			{
				$dashdotted = $conf->global->ULTIMATE_DASH_DOTTED;
			}
			$this->border_style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => $dashdotted , 'color' => $bordercolor);
		}

		// Get source company
		$this->emetteur = $mysoc;
		if (! $this->emetteur->country_code) $this->emetteur->country_code = substr($langs->defaultlang, -2);    // By default, if was not defined
		
	}


	/**
	 *      Fonction generant le projet sur le disque
	 *		@param	    object   		Object project a generer
	 *		@param		outputlangs		Lang output object
	 *		@return	    int         	1 if OK, <=0 if KO
	 */
	public function write_file($object, $outputlangs)
	{
		global $user, $langs, $conf, $hookmanager, $nblines;
		
		$textcolor = array('25','25','25');
		if (! empty($conf->global->ULTIMATE_TEXTCOLOR_COLOR))
		{
			$textcolor = html2rgb($conf->global->ULTIMATE_TEXTCOLOR_COLOR);
		}
		if (! empty($conf->global->ULTIMATE_SET_RADIUS))
		{
			$roundradius = $conf->global->ULTIMATE_SET_RADIUS;
		}
		if(! empty($conf->global->ULTIMATE_BORDERCOLOR_COLOR))
		{
			$bordercolor = html2rgb($conf->global->ULTIMATE_BORDERCOLOR_COLOR);
		}
		if (! empty($conf->global->ULTIMATE_DASH_DOTTED))
		{
			$dashdotted = $conf->global->ULTIMATE_DASH_DOTTED;
		}
		$this->border_style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => $dashdotted , 'color' => $bordercolor);

		if (! is_object($outputlangs)) $outputlangs=$langs;
		
		// Translations
		$outputlangs->loadLangs(array("main", "dict", "companies", "projects", "ultimatepdf@ultimatepdf"));
		
		$nblines = count($object->lines);
		
		if ($conf->projet->dir_output)
		{
			$objectref = dol_sanitizeFileName($object->ref);
			$dir = $conf->projet->dir_output;
			if (! preg_match('/specimen/i',$objectref)) $dir.= "/" . $objectref;
			$file = $dir . "/" . $objectref . ".pdf";

			if (! file_exists($dir))
			{
				if (dol_mkdir($dir) < 0)
				{
					$this->error=$langs->transnoentities("ErrorCanNotCreateDir",$dir);
					return 0;
				}
			}

			if (file_exists($dir))
			{
				// Add pdfgeneration hook
				if (! is_object($hookmanager))
				{
					include_once DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php';
					$hookmanager=new HookManager($this->db);
				}
				$hookmanager->initHooks(array('pdfgeneration'));
				$parameters=array('file'=>$file,'object'=>$object,'outputlangs'=>$outputlangs);
				global $action;
				$reshook=$hookmanager->executeHooks('beforePDFCreation',$parameters,$object,$action);    // Note that $action and $object may have been modified by some hooks
				
				// Create pdf instance
				$pdf=pdf_getInstance($this->format);
                $default_font_size = pdf_getPDFFontSize($outputlangs);	// Must be after pdf_getInstance
				$pdf->SetAutoPageBreak(1,0);
				
                $heightforinfotot = 50;	// Height reserved to output the info and total part
		        $heightforfreetext= (isset($conf->global->MAIN_PDF_FREETEXT_HEIGHT)?$conf->global->MAIN_PDF_FREETEXT_HEIGHT:5);	// Height reserved to output the free text on last page
	            $heightforfooter = $this->marge_basse + 8;	// Height reserved to output the footer (value include bottom margin)
				if ($conf->global->MAIN_GENERATE_DOCUMENTS_SHOW_FOOT_DETAILS >0) $heightforfooter+= 6;

                if (class_exists('TCPDF'))
                {
                    $pdf->setPrintHeader(false);
                    $pdf->setPrintFooter(false);
                }
                $pdf->SetFont(pdf_getPDFFont($outputlangs));
				// Set path to the background PDF File
                if (empty($conf->global->MAIN_DISABLE_FPDI) && ! empty($conf->global->MAIN_ADD_PDF_BACKGROUND))
                {
                    $pagecount = $pdf->setSourceFile($conf->mycompany->dir_output.'/'.$conf->global->MAIN_ADD_PDF_BACKGROUND);
                    $tplidx = $pdf->importPage(1);
                }

				// Complete object by loading several other informations
				$task = new Task($this->db);
				$tasksarray = $task->getTasksArray(0,0,$object->id);
				
				if (! $object->id > 0)  // Special case when used with object = specimen, we may return all lines
				{
					$tasksarray=array_slice($tasksarray, 0, min(5, count($tasksarray)));
				}

				$object->lines=$tasksarray;
				$nblines=count($object->lines);

				$pdf->Open();
				$pagenb=0;
				$pdf->SetDrawColor(128,128,128);

				//Generation de l entete du fichier
				$pdf->SetTitle($outputlangs->convToOutputCharset($object->ref));
				$pdf->SetSubject($outputlangs->transnoentities("Project"));
				$pdf->SetCreator("PowerERP ".DOL_VERSION);
				$pdf->SetAuthor($outputlangs->convToOutputCharset($user->getFullName($outputlangs)));
				$pdf->SetKeyWords($outputlangs->convToOutputCharset($object->ref)." ".$outputlangs->transnoentities("Project"));
				if (! empty($conf->global->MAIN_DISABLE_PDF_COMPRESSION)) $pdf->SetCompression(false);

				$pdf->SetMargins($this->marge_gauche, $this->marge_haute, $this->marge_droite);   // Left, Top, Right

				// New page
				$pdf->AddPage();
				if (! empty($tplidx)) $pdf->useTemplate($tplidx);
				$pagenb++;

				$top_shift = $this->_pagehead($pdf, $object, 1, $outputlangs, $hookmanager);
				$pdf->SetFont('', '', $default_font_size - 1);
				$pdf->MultiCell(0, 3, '');		// Set interline to 3
				$pdf->SetTextColorArray($textcolor);
				
				//catch logo height
				// Other Logo
				if (! empty ($conf->global->ULTIMATE_DESIGN) && ! empty ($conf->global->ULTIMATE_OTHERLOGO_FILE))
				{
					$id = $conf->global->ULTIMATE_DESIGN;
					$upload_dir	= $conf->ultimatepdf->dir_output.'/otherlogo/'.$id.'/';	
					$filearray = dol_dir_list($upload_dir, "files", 0, '', '(\.meta|_preview.*\.png)$', $sortfield, 0, 1);
					$otherlogo = $conf->ultimatepdf->dir_output.'/otherlogo/'.$id.'/'.$filearray[0]['name'];
				}		
				if (is_readable($otherlogo) && ! empty($filearray))
				{
					$logo_height = max(pdf_getUltimateHeightForOtherLogo($otherlogo, true), 20);					
				}
				else
				{
					// MyCompany logo
					$logo_height = max(pdf_getUltimateHeightForLogo($conf->global->ULTIMATE_LOGO_HEIGHT, true), 20);
				}

				//Set $hautcadre
				if (($conf->global->ULTIMATE_PDF_PROJECT_ADDALSOTARGETDETAILS == 1) || (! empty($conf->global->MAIN_INFO_SOCIETE_NOTE) && !empty($this->emetteur->note_private)) || (! empty($conf->global->MAIN_INFO_TVAINTRA) && !empty($this->emetteur->tva_intra)))
				{
					$hautcadre = 52;
				}
				else
				{
					$hautcadre = 48;
				}

				$tab_top = $this->marge_haute + $logo_height + $hautcadre + $top_shift + 15;
				$tab_top_newpage = (empty($conf->global->ULTIMATE_PROJECT_PDF_DONOTREPEAT_HEAD) ? $this->marge_haute + $logo_height + $top_shift + 10 : 10);
				
				$tab_height = 170 - $top_shift;
				$tab_height_newpage = 190;
				if (empty($conf->global->ULTIMATE_PROJECT_PDF_DONOTREPEAT_HEAD)) $tab_height_newpage -= $top_shift;
				$tab_width = $this->page_largeur-$this->marge_gauche-$this->marge_droite;

				// Display notes
				$notetoshow = empty($object->note_public) ? '' : $object->note_public;
				$pagenb = $pdf->getPage();
				if ($notetoshow && empty($conf->global->MAIN_PUBLIC_NOTE_IN_ADDRESS))
				{
					$pageposbeforenote = $pagenb;
					$nexY = $pdf->GetY();
					$tab_top = $nexY;
					
					$substitutionarray = pdf_getSubstitutionArray($outputlangs, null, $object);
					complete_substitutions_array($substitutionarray, $outputlangs, $object);
					$notetoshow = make_substitutions($notetoshow, $substitutionarray, $outputlangs);
					
					$pdf->startTransaction();
					
					$pdf->SetFont('','', $default_font_size - 1);   // Dans boucle pour gerer multi-page
					$pdf->writeHTMLCell($tab_width, 3, $this->marge_gauche+1, $tab_top, dol_htmlentitiesbr($notetoshow), 0, 1);
					// Description
				    $pageposafternote = $pdf->getPage();
				    $posyafter = $pdf->GetY();
					$nexY = $pdf->GetY();
					$height_note = $nexY - $tab_top;

					// Rect prend une longueur en 3eme et 4eme param
					$pdf->SetDrawColor(192,192,192);					
					$pdf->RoundedRect($this->marge_gauche, $tab_top-1, $tab_width, $height_note+1, $roundradius, $round_corner = '1111', 'S', $this->border_style, $bgcolor);

					if ($pageposafternote > $pageposbeforenote)
				    {
				        $pdf->rollbackTransaction(true);

				        // prepair pages to receive notes
				        while ($pagenb < $pageposafternote) 
						{
				            $pdf->AddPage();
				            $pagenb++;
				            if (! empty($tplidx)) $pdf->useTemplate($tplidx);
				            if (empty($conf->global->ULTIMATE_PROJECT_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
				            // $this->_pagefoot($pdf,$object,$outputlangs,1);
				            $pdf->setTopMargin($tab_top_newpage);
				            // The only function to edit the bottom margin of current page to set it.
				            $pdf->setPageOrientation('', 1, $heightforfooter);
				        }

				        // back to start
				        $pdf->setPage($pageposbeforenote);
				        $pdf->setPageOrientation('', 1, $heightforfooter);
				        $pdf->SetFont('','', $default_font_size - 1);
				        $pdf->writeHTMLCell($tab_width, 3, $this->marge_gauche+1, $tab_top, dol_htmlentitiesbr($notetoshow), 0, 1);
				        $pageposafternote=$pdf->getPage();

				        $posyafter = $pdf->GetY();
						$nexY = $pdf->GetY();

				        if ($posyafter > ($this->page_hauteur - $heightforfooter))	// There is no space left for total+free text
				        {
				            $pdf->AddPage('','',true);
				            $pagenb++;
				            $pageposafternote++;
				            $pdf->setPage($pageposafternote);
				            $pdf->setTopMargin($tab_top_newpage);
				            // The only function to edit the bottom margin of current page to set it.
				            $pdf->setPageOrientation('', 1, $heightforfooter);
				            //$posyafter = $tab_top_newpage;
				        }


				        // apply note frame to previus pages
				        $i = $pageposbeforenote;
				        while ($i < $pageposafternote) 
						{
				            $pdf->setPage($i);

				            $pdf->SetDrawColor(128,128,128);
				            // Draw note frame
				            if ($i > $pageposbeforenote)
							{
				                $height_note = $this->page_hauteur - ($tab_top_newpage + $heightforfooter);
								$pdf->RoundedRect($this->marge_gauche, $tab_top_newpage-1, $tab_width, $height_note+1, $roundradius, $round_corner = '1111', $this->border_style, $bgcolor);
				            }
				            else
							{
				                $height_note = $this->page_hauteur - ($tab_top + $heightforfooter);
								$pdf->RoundedRect($this->marge_gauche, $tab_top-1, $tab_width, $height_note+1, $roundradius, $round_corner = '1111', 'S', $this->border_style, $bgcolor);
				            }

				            // Add footer
				            $pdf->setPageOrientation('', 1, 0);	// The only function to edit the bottom margin of current page to set it.
				            $this->_pagefoot($pdf,$object,$outputlangs,1);

				            $i++;
				        }

				        // apply note frame to last page
				        $pdf->setPage($pageposafternote);
				        if (! empty($tplidx)) $pdf->useTemplate($tplidx);
				        if (empty($conf->global->ULTIMATE_PROJECT_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
				        $height_note=$posyafter-$tab_top_newpage;
				        $pdf->RoundedRect($this->marge_gauche, $tab_top_newpage-1, $tab_width, $height_note+1, $roundradius, $round_corner = '1111', 'S', $this->border_style, $bgcolor);
				    }
				    else // No pagebreak
				    {
				        $pdf->commitTransaction();
				        $posyafter = $pdf->GetY();
				        $height_note=$posyafter-$tab_top;
				        $pdf->RoundedRect($this->marge_gauche, $tab_top-1, $tab_width, $height_note+1, $roundradius, $round_corner = '1111', 'S', $this->border_style, $bgcolor);

				        if($posyafter > ($this->page_hauteur - ($heightforfooter+$heightforfreetext+20)) )
				        {
				            // not enough space, need to add page
				            $pdf->AddPage('','',true);
				            $pagenb++;
				            $pageposafternote++;
				            $pdf->setPage($pageposafternote);
				            if (! empty($tplidx)) $pdf->useTemplate($tplidx);
				            if (empty($conf->global->ULTIMATE_PROJECT_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);

				            $posyafter = $tab_top_newpage;
				        }
				    }

				    $tab_height = $tab_height - $height_note;
				    $tab_top = $posyafter + 10;
				}
				else
				{
					$height_note=0;
				}
				
				// Use new auto column system
				$this->prepareArrayColumnField($object, $outputlangs, $hidedetails, $hidedesc, $hideref);
				
				// Simulation de tableau pour connaitre la hauteur de la ligne de titre
				$pdf->startTransaction();
				$this->pdfTabTitles($pdf, $tab_top, $tab_height, $outputlangs, $hidetop);
				$pdf->rollbackTransaction(true);
				$nexY = $pdf->GetY();
				$tab_top = $nexY;
				if($desc_incoterms)
				{
					$tab_top += $height_incoterms + 6;
				}
				if($height_note)
				{
					$tab_top += $height_note + 2;
				}
				if(! $height_note && ! $desc_incoterms)
				{
					$tab_top = $nexY + 12;
				}
				if( $height_note && $desc_incoterms)
				{
					$tab_top -= 8;
				}
				
				$iniY = $tab_top + $this->tabTitleHeight + 2;
				$curY = $tab_top + $this->tabTitleHeight + 2;
				if (empty($conf->global->MAIN_PDF_DISABLE_COL_HEAD_TITLE))
				{
					$nexY = $tab_top + $this->tabTitleHeight - 8;
				}
				else
				{
					$nexY = $tab_top + $this->tabTitleHeight - 2;
				}

				// Loop on each lines
				$pageposbeforeprintlines = $pdf->getPage();
				$pagenb = $pageposbeforeprintlines;
				$line_number = 1;
				for ($i = 0 ; $i < $nblines ; $i++)
				{
					$objectligne = $object->lines[$i];
					
					$valide = $objectligne->id ? $objectligne->fetch($objectligne->id) : 0;
					
					if ($valide > 0 || $object->specimen)
					{
						$curX = $this->getColumnContentXStart('desc');
						$text_length=$this->getColumnContentXStart('planned_workload')-$this->getColumnContentXStart('desc');
						
						$curY = $nexY;
						$pdf->SetFont('','', $default_font_size - 2);   // Dans boucle pour gerer multi-page
						$pdf->SetTextColorArray($textcolor);	
						
						$pdf->setTopMargin($tab_top_newpage);
						//If we aren't on last lines footer space needed is on $heightforfooter
						if ($i != $nblines-1)
						{
							$bMargin=$heightforfooter;
						}
						else 
						{
							//We are on last item, need to check all footer (freetext, ...)
							$bMargin=$heightforfooter+$heightforfreetext+$heightforinfotot;
						}
						$pdf->setPageOrientation('', 1, $bMargin);	// The only function to edit the bottom margin of current page to set it.
						$pageposbefore=$pdf->getPage();
						
						$showpricebeforepagebreak=1;
						$posYStartDescription=0;
						$posYAfterDescription=0;

						// Description of ligne
						// $object->titre;$object->description;$object->date_start;$object->date_end;$object->date_close;$object->budget_amount;
						//$ref=$object->lines[$i]->ref;$object->lines[$i]->label;$object->lines[$i]->description;
						
						$libelleline=$objectligne->label;
						//$description=$object->lines[$i]->description;
						//$desc=dol_htmlentitiesbr($object->lines[$i]->description,1);   // Desc (not empty for free lines)
											
						// Label
						$pdf->startTransaction();
						$posYStartDescription = $curY;
						$pdf->writeHTMLCell($text_length, 0, $curX, $curY, $libelleline, 0, 1, 0);
						$posYAfterDescription=$pdf->GetY();						
						$pageposafter=$pdf->getPage();
						
						if ($pageposafter > $pageposbefore)	// There is a pagebreak
						{
							$pdf->rollbackTransaction(true);
							$pageposafter=$pageposbefore;
							
							$pdf->setPageOrientation('', 1, $heightforfooter);	// The only function to edit the bottom margin of current page to set it.
							// Label
							$posYStartDescription = $curY;
							$pdf->writeHTMLCell($text_length, 0, $curX, $curY, $libelleline, 0, 1, 0);
							$posYAfterDescription=$pdf->GetY();
							$pageposafter=$pdf->getPage();
							
							if ($posYAfterDescription > ($this->page_hauteur - $bMargin))	// There is no space left for total+free text
							{
								if ($i == ($nblines-1))	// No more lines, and no space left to show total, so we create a new page
								{
									$pdf->AddPage('','',true);
									if (! empty($tplidx)) $pdf->useTemplate($tplidx);
									if (empty($conf->global->ULTIMATE_PROJECT_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
									$pdf->setPage($pageposafter+1);
								}
							}
							else
							{
								// We found a page break
								$showpricebeforepagebreak=1;
							}
								
						}
						else	// No pagebreak
						{
							$pdf->commitTransaction();
						}
						$posYAfterDescription=$pdf->GetY();
						
						$nexY = $pdf->GetY()+2;

						$pageposafter=$pdf->getPage();

						$pdf->setPage($pageposbefore);
						$pdf->setTopMargin($this->marge_haute);
						$pdf->setPageOrientation('', 1, 0);	// The only function to edit the bottom margin of current page to set it.

						// We suppose that a too long description is moved completely on next page
						if ($pageposafter>$pageposbefore && empty($showpricebeforepagebreak)) 
						{
							$pdf->setPage($pageposafter); $curY = $tab_top_newpage;
						}

						$pdf->SetFont('','',  $default_font_size - 2);   // On repositionne la police par defaut
						
						if ($posYStartDescription>$posYAfterDescription && $pageposafter>$pageposbefore)
						{
							$pdf->setPage($pageposbefore); $curY = $posYStartDescription+1;
						}
						if ($curY > ($this->page_hauteur - $bMargin))	// There is no space left for total+free text
						{
							$pdf->setPage($pageposafter); $curY = $tab_top_newpage;
						}
						
						//Line numbering
						if (! empty($conf->global->ULTIMATE_PROJECT_WITH_LINE_NUMBER))
						{
							// Numbering
							if ($this->getColumnStatus('num') && array_key_exists($i,$object->lines))
							{
								$this->printStdColumnContent($pdf, $curY, 'num', $line_number);
								$nexY = max($pdf->GetY(),$nexY);
								$line_number++;
							}
						}
						
						// Ref of task
						if ($this->getColumnStatus('ref'))
						{
							$ref = $object->lines[$i]->ref;
							$this->printStdColumnContent($pdf, $curY, 'ref', $ref);
							$nexY = max($pdf->GetY(),$nexY);
						}
						
						// Workload
						if ($this->getColumnStatus('planned_workload'))
						{
							$planned_workload=convertSecondToTime((int) $objectligne->planned_workload,'allhourmin');
							$this->printStdColumnContent($pdf, $curY, 'planned_workload', $planned_workload);
							$nexY = max($pdf->GetY(),$nexY);
						}

						// Progress
						if ($this->getColumnStatus('progress'))
						{
							$progress=($object->lines[$i]->progress?$object->lines[$i]->progress.'%':'');
							$this->printStdColumnContent($pdf, $curY, 'progress', $progress);
							$nexY = max($pdf->GetY(),$nexY);
						}
						
						// Date start
						if ($this->getColumnStatus('datestart'))
						{
							$datestart=dol_print_date($object->lines[$i]->date_start,'day');
							$this->printStdColumnContent($pdf, $curY, 'datestart', $datestart);
							$nexY = max($pdf->GetY(),$nexY);
						}
						
						// Date end
						if ($this->getColumnStatus('dateend'))
						{
							$dateend=dol_print_date($object->lines[$i]->date_end,'day');
							$this->printStdColumnContent($pdf, $curY, 'dateend', $dateend);
							$nexY = max($pdf->GetY(),$nexY);
						}
						
						$pageposafter=$pdf->getPage();

						$pdf->SetFont('','', $default_font_size - 1);   // On repositionne la police par defaut
						$nexY = $pdf->GetY();

						// Add line
						if (! empty($conf->global->ULTIMATE_PROJECT_PDF_DASH_BETWEEN_LINES) && $i < ($nblines - 1))
						{
							$pdf->setPage($pageposafter);
							$pdf->SetLineStyle(array('dash'=>'1,1','color'=>array(210,210,210)));
							$pdf->line($this->marge_gauche, $nexY+4, $this->page_largeur - $this->marge_droite, $nexY+4);
							$pdf->SetLineStyle(array('dash'=>0));
						}

						$nexY+=4;    // Passe espace entre les lignes

						// Detect if some page were added automatically and output _tableau for past pages
						while ($pagenb < $pageposafter)
						{
							$pdf->setPage($pagenb);
							if ($pagenb == $pageposbeforeprintlines)
							{
								$this->_tableau($pdf, $tab_top, $this->page_hauteur - $tab_top - $heightforfooter, 0, $outputlangs, 0, 1, $object->multicurrency_code);
							}
							else
							{
								$this->_tableau($pdf, $tab_top_newpage, $this->page_hauteur - $tab_top_newpage - $heightforfooter, 0, $outputlangs, 0, 1, $object->multicurrency_code);
							}
							$this->_pagefoot($pdf,$object,$outputlangs,1);
							$pagenb++;
							$pdf->setPage($pagenb);
							$pdf->setPageOrientation('', 1, 0);	// The only function to edit the bottom margin of current page to set it.
							if (empty($conf->global->ULTIMATE_PROJECT_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
						}
						if (isset($object->lines[$i+1]->pagebreak) && $object->lines[$i+1]->pagebreak)
						{
							if ($pagenb == $pageposafter)
							{
								$this->_tableau($pdf, $tab_top, $this->page_hauteur - $tab_top - $heightforfooter, 0, $outputlangs, 0, 1, $object->multicurrency_code);
							}
							else
							{
								$this->_tableau($pdf, $tab_top_newpage, $this->page_hauteur - $tab_top_newpage - $heightforfooter, 0, $outputlangs, 0, 1, $object->multicurrency_code);
							}
							$this->_pagefoot($pdf,$object,$outputlangs,1);
							// New page
							$pdf->AddPage();
							if (! empty($tplidx)) $pdf->useTemplate($tplidx);
							$pagenb++;
							if (empty($conf->global->ULTIMATE_PROJECT_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
						}
					}
				}

				// Show square
				if ($pagenb == $pageposbeforeprintlines)
				{
					$this->_tableau($pdf, $tab_top, $this->page_hauteur - $tab_top - $heightforinfotot - $heightforfreetext - $heightforfooter, 0, $outputlangs, 0, 0, $object->multicurrency_code);
				}
				else
				{
					$this->_tableau($pdf, $tab_top_newpage, $this->page_hauteur - $tab_top_newpage - $heightforinfotot - $heightforfreetext - $heightforfooter, 0, $outputlangs, 0, 0, $object->multicurrency_code);
				}
				$bottomlasttab=$this->page_hauteur - $heightforinfotot - $heightforfreetext - $heightforfooter + 1;

				// Pied de page
				$this->_pagefoot($pdf,$object,$outputlangs);
				if (method_exists($pdf,'AliasNbPages')) $pdf->AliasNbPages();

				$pdf->Close();

				$pdf->Output($file,'F');

				// Actions on extra fields (by external module or standard code)
				if (! is_object($hookmanager))
				{
					include_once(DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php');
					$hookmanager=new HookManager($this->db);
				}
				$hookmanager->initHooks(array('pdfgeneration'));
				$parameters=array('file'=>$file,'object'=>$object,'outputlangs'=>$outputlangs);
				global $action;
				$reshook=$hookmanager->executeHooks('afterPDFCreation', $parameters, $this, $action);    // Note that $action and $object may have been modified by some hooks

				if (! empty($conf->global->MAIN_UMASK))
				@chmod($file, octdec($conf->global->MAIN_UMASK));
			
				$this->result = array('fullpath'=>$file);

				return 1;   // Pas d'erreur
			}
			else
			{
				$this->error=$langs->transnoentities("ErrorCanNotCreateDir",$dir);
				return 0;
			}
		}
		else
		{
			$this->error=$langs->transnoentities("ErrorConstantNotDefined","PROJECT_OUTPUTDIR");
				return 0;
		}
		$this->error=$langs->trans("ErrorUnknown");
		return 0;   // Erreur par defaut
	}


	/**
	 *   Show table for lines
	 *
	 *   @param		PDF			&$pdf     		Object PDF
	 *   @param		string		$tab_top		Top position of table
	 *   @param		string		$tab_height		Height of table (rectangle)
	 *   @param		int			$nexY			Y (not used)
	 *   @param		Translate	$outputlangs	Langs object
	 *   @param		int			$hidetop		1=Hide top bar of array and title, 0=Hide nothing, -1=Hide only title
	 *   @param		int			$hidebottom		Hide bottom bar of array
	 *   @return	void
	 */
	function _tableau(&$pdf, $tab_top, $tab_height, $nexY, $outputlangs, $hidetop = 0, $hidebottom = 0)
	{
		global $conf, $langs;

		// Force to disable hidetop and hidebottom
		$hidebottom = 0;
		if ($hidetop) $hidetop = - 1;

		$default_font_size = pdf_getPDFFontSize($outputlangs);
		if (! empty($conf->global->ULTIMATE_DASH_DOTTED))
		{
			$dashdotted = $conf->global->ULTIMATE_DASH_DOTTED;
		}
		if(! empty($conf->global->ULTIMATE_BORDERCOLOR_COLOR))
		{
			$bordercolor = html2rgb($conf->global->ULTIMATE_BORDERCOLOR_COLOR);
		}
		$bgcolor = array('170', '212', '255');
		if (! empty($conf->global->ULTIMATE_BGCOLOR_COLOR))
		{
			$bgcolor = html2rgb($conf->global->ULTIMATE_BGCOLOR_COLOR);
		}
		$opacity = 0.5;
		if (! empty($conf->global->ULTIMATE_BGCOLOR_OPACITY))
		{
			$opacity = $conf->global->ULTIMATE_BGCOLOR_OPACITY;
		}
		if (! empty($conf->global->ULTIMATE_SET_RADIUS))
		{
			$roundradius = $conf->global->ULTIMATE_SET_RADIUS;
		}
		$textcolor = array('25', '25', '25');
		if (! empty($conf->global->ULTIMATE_TEXTCOLOR_COLOR))
		{
			$textcolor = html2rgb($conf->global->ULTIMATE_TEXTCOLOR_COLOR);
		}
		$this->border_style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' =>$dashdotted , 'color' => $bordercolor);

		// Amount in (at tab_top - 1)
        $pdf->SetFillColorArray($bgcolor);
		$pdf->SetTextColorArray($textcolor);
        $pdf->SetFont($conf->global->MAIN_PDF_FORCE_FONT, '', $default_font_size - 2);

		if ($roundradius == 0)
		{
			$roundradius = 0.1; //RoundedRect don't support $roundradius to be 0
		}
		// Output RoundedRect
		$pdf->SetAlpha($opacity);
		$pdf->RoundedRect($this->marge_gauche, $tab_top - 8, $this->page_largeur-$this->marge_gauche-$this->marge_droite, 8, $roundradius, $round_corner = '1001', 'FD', $this->border_style, $bgcolor);
		$pdf->SetAlpha(1);
		//title line
		$pdf->RoundedRect($this->marge_gauche, $tab_top, $this->page_largeur-$this->marge_gauche-$this->marge_droite, $tab_height, $roundradius, $round_corner = '0110', 'S', $this->border_style, $bgcolor);

		$this->pdfTabTitles($pdf, $tab_top - 8, $tab_height + 8, $outputlangs, $hidetop);
		
    }

	/**
	 *   	Show header of page
	 *
	 *   	@param      $pdf     		Object PDF
	 *   	@param      $object     	Object project
	 *      @param      $showaddress    0=no, 1=yes
	 *      @param      $outputlangs	Object lang for output
	 */
	function _pagehead(&$pdf, $object, $showaddress, $outputlangs)
	{
		global $langs,$conf,$mysoc, $db;
		
		// Translations
		$outputlangs->loadLangs(array("main", "projects", "commercial", "ultimatepdf@ultimatepdf"));

		$default_font_size = pdf_getPDFFontSize($outputlangs);
		if (!empty($conf->global->ULTIMATE_DASH_DOTTED))
		{
			$dashdotted = $conf->global->ULTIMATE_DASH_DOTTED;
		}
		$bgcolor = array('170','212','255');
		if (!empty($conf->global->ULTIMATE_BGCOLOR_COLOR))
		{
			$bgcolor =  html2rgb($conf->global->ULTIMATE_BGCOLOR_COLOR);
		}
		if (!empty($conf->global->ULTIMATE_SENDER_STYLE))
		{
			$senderstyle = $conf->global->ULTIMATE_SENDER_STYLE;
		}
		if (!empty($conf->global->ULTIMATE_RECEIPT_STYLE))
		{
			$receiptstyle = $conf->global->ULTIMATE_RECEIPT_STYLE;
		}
		if(!empty($conf->global->ULTIMATE_BORDERCOLOR_COLOR))
		{
			$bordercolor = html2rgb($conf->global->ULTIMATE_BORDERCOLOR_COLOR);
		}
		$opacity = 0.5;
		if (!empty($conf->global->ULTIMATE_BGCOLOR_OPACITY))
		{
			$opacity =  $conf->global->ULTIMATE_BGCOLOR_OPACITY;
		}
		if (!empty($conf->global->ULTIMATE_SET_RADIUS))
		{
			$roundradius = $conf->global->ULTIMATE_SET_RADIUS;
		}
		$textcolor = array('25','25','25');
		if (!empty($conf->global->ULTIMATE_TEXTCOLOR_COLOR))
		{
			$textcolor =  html2rgb($conf->global->ULTIMATE_TEXTCOLOR_COLOR);
		}
		$qrcodecolor = array('25','25','25');
		if (!empty($conf->global->ULTIMATE_TEXTCOLOR_COLOR))
		{
			$qrcodecolor =  html2rgb($conf->global->ULTIMATE_QRCODECOLOR_COLOR);
		}	

		pdf_pagehead($pdf, $outputlangs, $this->page_hauteur);
		$main_page = $this->page_largeur-$this->marge_gauche-$this->marge_droite;

		//Affiche le filigrane brouillon - Print Draft Watermark
		if($object->statut==0 && (! empty($conf->global->SENDING_DRAFT_WATERMARK)) )
		{
            pdf_watermark($pdf,$outputlangs,$this->page_hauteur,$this->page_largeur,'mm',$conf->global->SENDING_DRAFT_WATERMARK);
		}
		
		// Add symbol of currency 
		$cursymbolbefore=$cursymbolafter='';
		if ($object->multicurrency_code)
		{
			$currency_symbol=$langs->getCurrencySymbol($object->multicurrency_code);
			$listofcurrenciesbefore=array('$','£','S/.','¥');
			if (in_array($currency_symbol,$listofcurrenciesbefore)) $cursymbolbefore.=$currency_symbol;
			else
			{
				$tmpcur=$currency_symbol;
				$cursymbolafter.=($tmpcur == $currency_symbol ? ' '.$tmpcur : $tmpcur);
			}
		}
		else
		{
			$cursymbolafter = $langs->getCurrencySymbol($conf->currency);
		}
		
		//Prepare la suite
        $pdf->SetTextColorArray($textcolor);
        $pdf->SetFont('', 'B', $default_font_size - 1);

        $posy = $this->marge_haute;
		$posx = $this->page_largeur - $this->marge_droite - 100;

        $pdf->SetXY($this->marge_gauche, $posy);

		// Other Logo
		$id = $conf->global->ULTIMATE_DESIGN;
        $upload_dir	= $conf->ultimatepdf->dir_output.'/otherlogo/'.$id.'/';	
        $filearray = dol_dir_list($upload_dir, "files", 0, '', '(\.meta|_preview.*\.png)$', 'name', 0, 1);
        $otherlogo = $conf->ultimatepdf->dir_output.'/otherlogo/'.$id.'/'.$filearray[0]['name'];
        
        if (! empty ($conf->global->ULTIMATE_DESIGN) && ! empty ($conf->global->ULTIMATE_OTHERLOGO_FILE) && is_readable($otherlogo) && ! empty($filearray))
        {
            $logo_height = max(pdf_getUltimateHeightForOtherLogo($otherlogo, true), 20);
            $pdf->Image($otherlogo, $this->marge_gauche, $posy, 0, $logo_height);	// width=0 (auto)
        }
		else
		{
			// Logo					
			if (empty($conf->global->PDF_DISABLE_MYCOMPANY_LOGO))
			{
				if ($this->emetteur->logo)
				{						
					$logodir = $conf->mycompany->dir_output;
					if (! empty($conf->mycompany->multidir_output[$object->entity])) $logodir = $conf->mycompany->multidir_output[$object->entity];
					if (empty($conf->global->MAIN_PDF_USE_LARGE_LOGO))
					{
						$logo = $logodir.'/logos/thumbs/'.$this->emetteur->logo_small;
					}
					else 
					{
						$logo = $logodir.'/logos/'.$this->emetteur->logo;
					}
					if (is_readable($logo))
					{
						$logo_height = max(pdf_getUltimateHeightForLogo($logo, true), 20);
						$pdf->Image($logo, $this->marge_gauche, $posy, 0, $logo_height);	// width=0 (auto)
					}
					else
					{
						$pdf->SetTextColor(200, 0, 0);
						$pdf->SetFont('', 'B', $default_font_size - 2);
						$pdf->RoundedRect($this->marge_gauche, $this->marge_haute, 100, 20, $roundradius, $round_corner = '1111', $senderstyle, $this->border_style, $bgcolor);
						$pdf->MultiCell(100, 3, $outputlangs->transnoentities("ErrorLogoFileNotFound", $logo), 0, 'L');
						$pdf->MultiCell(100, 3, $outputlangs->transnoentities("ErrorGoToGlobalSetup"), 0, 'L');
					}
				}
				else
				{
					$pdf->RoundedRect($this->marge_gauche, $this->marge_haute, 100, 20, $roundradius, $round_corner = '1111', $senderstyle, $this->border_style, $bgcolor);
					$text = $this->emetteur->name;
					$pdf->MultiCell(100, 4, $outputlangs->convToOutputCharset($text), 0, 'L');
					$logo_height = 20;
				}
			}
		}

		//Nom du Document
		$pdf->SetXY($this->marge_gauche, $this->marge_haute);
		$pdf->SetFont('', 'B', $default_font_size + 3);
		$pdf->SetTextColorArray($textcolor);
		$pdf->MultiCell(0, 3, $outputlangs->transnoentities("Project")." ".$outputlangs->convToOutputCharset($object->ref), '', 'R');
		
		$pdf->SetFont('', '', $default_font_size + 2);
		
		$posy = $pdf->getY();
		$top_shift = 0;

		if ($showaddress)
		{
			// Sender properties
			$carac_emetteur .= pdf_element_build_address($outputlangs, $this->emetteur, $object->thirdparty, '', 0, 'source', $object);

			// Show sender
			$posy = $logo_height + $this->marge_haute + $top_shift;	
			$posx = $this->marge_gauche;

			if (($conf->global->ULTIMATE_PDF_PROJECT_ADDALSOTARGETDETAILS == 1) || (! empty($conf->global->MAIN_INFO_SOCIETE_NOTE) && !empty($this->emetteur->note_private)) || (! empty($conf->global->MAIN_INFO_TVAINTRA) && !empty($this->emetteur->tva_intra)))
			{
				$hautcadre = 52;
			}
			else
			{
				$hautcadre = 48;
			}

			$widthrecbox = $conf->global->ULTIMATE_WIDTH_RECBOX;
			if (! empty($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT) && ($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT != "no")) $posx=$this->page_largeur-$this->marge_droite-$widthrecbox; 
			
			if ($roundradius == 0) 
			{
				$roundradius = 0.1; //RoundedRect don't support $roundradius to be 0
			}
			$this->border_style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => $dashdotted , 'color' => $bordercolor);
			// Show sender frame
			$pdf->SetTextColorArray($textcolor);
			$pdf->SetFont('','', $default_font_size - 1);
			$pdf->SetAlpha($opacity);
			$pdf->RoundedRect($posx, $posy, $widthrecbox, $hautcadre, $roundradius, $round_corner = '1111', $senderstyle, $this->border_style, $bgcolor);
			$pdf->SetAlpha(1);

			// Show sender name
	        $pdf->SetXY($posx + 2, $posy + 3);
			$pdf->SetFont('', 'B', $default_font_size - 1);
			if (!empty($conf->global->ULTIMATE_PDF_ALIAS_COMPANY))
			{
				$pdf->MultiCell($widthrecbox-5, 4, $outputlangs->convToOutputCharset($conf->global->ULTIMATE_PDF_ALIAS_COMPANY), 0, 'L');
			}
			else
			{
				$pdf->MultiCell($widthrecbox-5, 4, $outputlangs->convToOutputCharset($this->emetteur->name), 0, 'L');
			}
				
			$posy = $pdf->getY();

			// Show sender information
			$pdf->SetXY($posx+2,$posy);
			$pdf->SetFont('','', $default_font_size - 1);
			$pdf->writeHTMLCell($widthrecbox-5, 4, $posx+2, $posy, $carac_emetteur, 0, 2, 0, true, 'L', true);
			$posy=$pdf->getY();
			
			// Show private note from societe
			if (! empty($conf->global->MAIN_INFO_SOCIETE_NOTE) && ! empty($this->emetteur->note_private))
    		{
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->SetXY($posx+2,$posy);
				$pdf->writeHTMLCell($widthrecbox-5, 8, $posx+2, $posy+2, dol_string_nohtmltag($this->emetteur->note_private), 0, 1, 0, true, 'L', true);
			}

			// If PROJECTCONTRIBUTOR and PROJECTLEADER contact defined, we use it
			if ($object->getIdContact('external','PROJECTCONTRIBUTOR') && $object->getIdContact('external','PROJECTLEADER'))
			{
				// If PROJECTCONTRIBUTOR contact defined on invoice, we use it
				$usecontact=false;
				$arrayidcontact=$object->getIdContact('external','PROJECTCONTRIBUTOR');
				if (count($arrayidcontact) > 0);
				{
					$usecontact=true;
					$result=$object->fetch_contact($arrayidcontact[0]);
				}

				// Recipient name
				// On peut utiliser le nom de la societe du contact
				if ($usecontact && !empty($conf->global->MAIN_USE_COMPANY_NAME_OF_CONTACT)) 
				{
					$thirdparty = $object->contact;
				} 
				else 
				{
					$thirdparty = $object->thirdparty;
				}

				$carac_client_name= pdfBuildThirdpartyName($thirdparty, $outputlangs);
				
				// Recipient address
				$carac_client=pdf_element_build_address($outputlangs, $this->emetteur, $thirdparty, $object->contact, $usecontact, 'target', $object);

				// Show recipient
				$widthrecboxrecipient=$this->page_largeur-$this->marge_droite-$this->marge_gauche-$conf->global->ULTIMATE_WIDTH_RECBOX;
				$posy=$logo_height+$this->marge_haute+$top_shift;
				$posx=$this->page_largeur-$this->marge_droite-$widthrecboxrecipient;
				if (! empty($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT) && ($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT != "no")) $posx=$this->marge_gauche;

				// Show recipient frame
				$pdf->SetTextColorArray($textcolor);
				$pdf->SetFont('','', $default_font_size - 2);

				// Show invoice address
				$pdf->SetAlpha($opacity);
				$pdf->RoundedRect($posx, $posy, $widthrecboxrecipient/2, $hautcadre, $roundradius, $round_corner = '1111', $receiptstyle, $this->border_style, $bgcolor);
				$pdf->SetAlpha(1);
				$pdf->SetXY($posx-$widthrecboxrecipient/2, $posy-4);
				$pdf->MultiCell($widthrecboxrecipient, 4, $outputlangs->transnoentities("TypeContact_project_external_PROJECTCONTRIBUTOR"), 0, 'R');

				// Show recipient name
				$pdf->SetXY($posx+2, $posy+2);
				$pdf->SetFont('','B', $default_font_size);
				$pdf->MultiCell($widthrecboxrecipient-5, 4, $carac_client_name, 0, 'L');
				
				$posy = $pdf->getY();
				
				// Show recipient information
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->SetXY($posx+2, $posy);
				$pdf->writeHTMLCell($widthrecboxrecipient-5, 4, $posx+2, $posy, $carac_client, 0, 2, 0, true, 'L', true);	
				
				// If PROJECTLEADER contact defined on invoice, we use it
				$usecontact=false;
				$arrayidcontact=$object->getIdContact('external','PROJECTLEADER');
			
				if (count($arrayidcontact) > 0)
				{
					$usecontact=true;
					$result=$object->fetch_contact($arrayidcontact[0]);
				}

				// On peut utiliser le nom de la societe du contact
				if ($usecontact && !empty($conf->global->MAIN_USE_COMPANY_NAME_OF_CONTACT)) 
				{
					$thirdparty = $object->contact;
				} 
				else 
				{
					$thirdparty = $object->thirdparty;
				}

				$carac_client_name= pdfBuildThirdpartyName($thirdparty, $outputlangs);
				
				$carac_client=pdf_element_build_address($outputlangs, $this->emetteur, $thirdparty, $object->contact, $usecontact, 'target', $object);
				
				// Show recipient frame
				$pdf->SetTextColorArray($textcolor);
				$pdf->SetFont('','', $default_font_size - 2);

				// Show shipping address
				$posy = $logo_height + $this->marge_haute + $top_shift;
				$posx = $posx + $widthrecboxrecipient / 2;
				$pdf->SetAlpha($opacity);
				$pdf->RoundedRect($posx, $posy, $widthrecboxrecipient/2, $hautcadre, $roundradius, $round_corner = '1111', $receiptstyle, $this->border_style, $bgcolor);
				$pdf->SetAlpha(1);
				$pdf->SetXY($posx-$widthrecboxrecipient/2, $posy-4);
				$pdf->MultiCell($widthrecboxrecipient, 4, $outputlangs->transnoentities("TypeContact_project_external_PROJECTLEADER"), 0, 'R');	
				
				// Show recipient name
				$pdf->SetXY($posx+2, $posy+2);
				$pdf->SetFont('','B', $default_font_size);
				$pdf->MultiCell($widthrecboxrecipient-5, 4, $carac_client_name, 0, 'L');
				
				$posy = $pdf->getY();

				// Show recipient information
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->SetXY($posx+2, $posy);
				$pdf->writeHTMLCell($widthrecboxrecipient-5, 4, $posx+2, $posy, $carac_client, 0, 2, 0, true, 'L', true);
			}
			elseif ($object->getIdContact('external','PROJECTCONTRIBUTOR'))
			{
				// If PROJECTCONTRIBUTOR contact defined on project, we use it
				$usecontact=false;
				$arrayidcontact=$object->getIdContact('external','PROJECTCONTRIBUTOR');
				if (count($arrayidcontact) > 0)
				{
					$usecontact=true;
					$result=$object->fetch_contact($arrayidcontact[0]);
				}

				// Recipient name
				// On peut utiliser le nom de la societe du contact
				if ($usecontact && !empty($conf->global->MAIN_USE_COMPANY_NAME_OF_CONTACT)) 
				{
					$thirdparty = $object->contact;
				} 
				else 
				{
					$thirdparty = $object->thirdparty;
				}

				$carac_client_name= pdfBuildThirdpartyName($thirdparty, $outputlangs);
				

				$carac_client=pdf_element_build_address($outputlangs, $this->emetteur, $thirdparty, $object->contact, $usecontact, 'target', $object);

				// Show recipient
				$widthrecboxrecipient=$this->page_largeur-$this->marge_droite-$this->marge_gauche-$conf->global->ULTIMATE_WIDTH_RECBOX-2;
				$posy=$logo_height+$this->marge_haute+$top_shift;		
				$posx=$this->page_largeur-$this->marge_droite-$widthrecboxrecipient;	
				if (! empty($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT) && ($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT != "no")) $posx=$this->marge_gauche;
				
				// Show recipient frame
				$pdf->SetTextColorArray($textcolor);
				$pdf->SetFont('','', $default_font_size - 2);

				// Show project contributor address
				$pdf->SetAlpha($opacity);
				$pdf->RoundedRect($posx, $posy, $widthrecboxrecipient, $hautcadre, $roundradius, $round_corner = '1111', $receiptstyle, $this->border_style, $bgcolor);
				$pdf->SetAlpha(1);
				$pdf->SetXY($posx, $posy-4);		
				$pdf->MultiCell($widthrecboxrecipient, 4, $outputlangs->transnoentities("TypeContact_project_external_PROJECTCONTRIBUTOR"), 0, 'R');
				
				$posy = $pdf->getY();
				
				// Show recipient name
				$pdf->SetXY($posx+2, $posy+3);
				$pdf->SetFont('','B', $default_font_size);
				$pdf->MultiCell($widthrecboxrecipient-5, 4, $carac_client_name, 0, 'L');
				
				$posy = $pdf->getY();
				
				// Show recipient information
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->SetXY($posx+2,$posy);
				$pdf->writeHTMLCell($widthrecboxrecipient-5, 4, $posx+2, $posy, $carac_client, 0, 2, 0, true, 'L', true);
			}
			elseif ($object->getIdContact('external','PROJECTLEADER'))
			{
				// If PROJECTLEADER contact defined on project, we use it
				$usecontact=false;
				$arrayidcontact=$object->getIdContact('external','PROJECTLEADER');
				if (count($arrayidcontact) > 0)
				{
					$usecontact=true;
					$result=$object->fetch_contact($arrayidcontact[0]);
				}

				// Recipient name
				// On peut utiliser le nom de la societe du contact
				if ($usecontact && !empty($conf->global->MAIN_USE_COMPANY_NAME_OF_CONTACT)) 
				{
					$thirdparty = $object->contact;
				} 
				else 
				{
					$thirdparty = $object->thirdparty;
				}

				$carac_client_name= pdfBuildThirdpartyName($thirdparty, $outputlangs);
				

				$carac_client=pdf_element_build_address($outputlangs, $this->emetteur, $thirdparty, $object->contact, $usecontact, 'target', $object);

				// Show recipient
				$widthrecboxrecipient=$this->page_largeur-$this->marge_droite-$this->marge_gauche-$conf->global->ULTIMATE_WIDTH_RECBOX-2;
				$posy=$logo_height+$this->marge_haute+$top_shift;		
				$posx=$this->page_largeur-$this->marge_droite-$widthrecboxrecipient;	
				if (! empty($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT) && ($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT != "no")) $posx=$this->marge_gauche;
				
				// Show recipient frame
				$pdf->SetTextColorArray($textcolor);
				$pdf->SetFont('','', $default_font_size - 2);

				// Show project leader address
				$pdf->SetAlpha($opacity);
				$pdf->RoundedRect($posx, $posy, $widthrecboxrecipient, $hautcadre, $roundradius, $round_corner = '1111', $receiptstyle, $this->border_style, $bgcolor);
				$pdf->SetAlpha(1);
				$pdf->SetXY($posx, $posy-4);		
				$pdf->MultiCell($widthrecboxrecipient, 4, $outputlangs->transnoentities("TypeContact_project_external_PROJECTLEADER"), 0, 'R');
				
				$posy = $pdf->getY();
				
				// Show recipient name
				$pdf->SetXY($posx+2, $posy+3);
				$pdf->SetFont('','B', $default_font_size);
				$pdf->MultiCell($widthrecboxrecipient-5, 4, $carac_client_name, 0, 'L');
				
				$posy = $pdf->getY();
				
				// Show recipient information
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->SetXY($posx+2,$posy);
				$pdf->writeHTMLCell($widthrecboxrecipient-5, 4, $posx+2, $posy, $carac_client, 0, 2, 0, true, 'L', true);
			}
			else
			{
				$thirdparty = $object->thirdparty;
				// Recipient name
				$carac_client_name= pdfBuildThirdpartyName($thirdparty, $outputlangs);
				// Recipient address
				$carac_client=pdf_element_build_address($outputlangs, $this->emetteur, $object->thirdparty, '', 0, 'target', $object);

				// Show recipient
				$widthrecboxrecipient=$this->page_largeur-$this->marge_droite-$this->marge_gauche-$conf->global->ULTIMATE_WIDTH_RECBOX-2;
				$posy=$logo_height+$this->marge_haute+$top_shift;
				$posx=$this->page_largeur-$this->marge_droite-$widthrecboxrecipient;
				if (! empty($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT) && ($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT != "no")) $posx=$this->marge_gauche;

				// Show recipient frame
				$pdf->SetTextColorArray($textcolor);
				$pdf->SetFont('','', $default_font_size - 2);
				
				// Show shipping address
				$pdf->SetXY($posx, $posy-4);	
				$pdf->SetAlpha($opacity);
				$pdf->RoundedRect($posx, $posy, $widthrecboxrecipient, $hautcadre, $roundradius, $round_corner = '1111', $receiptstyle, $this->border_style, $bgcolor);
				$pdf->SetAlpha(1);

				// Show recipient name
				$pdf->SetXY($posx+2, $posy+3);
				$pdf->SetFont('','B', $default_font_size);
				$pdf->MultiCell($widthrecboxrecipient-5, 4, $carac_client_name, 0, 'L');
				
				$posy = $pdf->getY();
				
				// Show recipient information
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->SetXY($posx+2, $posy);
				$pdf->writeHTMLCell($widthrecboxrecipient-5, 4, $posx+2, $posy, $carac_client, 0, 2, 0, true, 'L', true);			
			}
			
			// Other informations

			$pdf->SetFillColor(255,255,255);

			// Opportunity Status
			$width = $main_page / 5 - 1.5;
			$RoundedRectHeight = $this->marge_haute + $logo_height + $hautcadre + $top_shift + 2;
			$pdf->SetAlpha($opacity);			
			$pdf->RoundedRect($this->marge_gauche, $RoundedRectHeight, $width, 6, $roundradius, $round_corner = '1001', 'FD', $this->border_style, $bgcolor);
			$pdf->SetAlpha(1);
	        $pdf->SetFont('','B', $default_font_size - 2);
	        $pdf->SetXY($this->marge_gauche,$RoundedRectHeight+0.5);
	        $pdf->SetTextColorArray($textcolor);
			$text='<div style="line-height:90%;">'.$outputlangs->transnoentities("OpportunityStatus").'</div>';
	        $pdf->writeHTMLCell($width, 5,$this->marge_gauche,$RoundedRectHeight+0.5, $text, 0, 0, false, true, 'C', true);

	        if (! empty($object->opp_status))
	        {
				$code = dol_getIdFromCode($this->db, $object->opp_status, 'c_lead_status', 'rowid', 'code');
	        	$pdf->SetFont('','', $default_font_size - 2);
	        	$pdf->SetXY($this->marge_gauche,$RoundedRectHeight+6);
	        	$pdf->SetTextColorArray($textcolor);
	        	$availability=$form->cache_availability[$object->availability_id]['label'];
				$pdf->writeHTMLCell($width, 6, $this->marge_gauche, $RoundedRectHeight+6, $langs->trans("OppStatus".$code), 0, 0, false, true, 'C', true);
	        }
			else
			{
				$pdf->MultiCell($width, 6, '', '0', 'C');
			}

	        // Opportunity Probability
			$pdf->SetAlpha($opacity);
			$pdf->RoundedRect($this->marge_gauche+$width+2, $RoundedRectHeight, $width, 6, $roundradius, $round_corner = '1001', 'FD', $this->border_style, $bgcolor);
			$pdf->SetAlpha(1);
	        $pdf->SetFont('','B', $default_font_size - 2);
	        $pdf->SetXY($this->marge_gauche+$width+2,$RoundedRectHeight);
	        $pdf->SetTextColorArray($textcolor);
	        $pdf->MultiCell($width, 5, $outputlangs->transnoentities("OpportunityProbability"), 0, 'C', false);

	        if (! empty($object->opp_percent))
	        {
	        	$pdf->SetFont('','', $default_font_size - 2);
	        	$pdf->SetXY($this->marge_gauche+$width+2,$RoundedRectHeight+6);
	        	$pdf->SetTextColorArray($textcolor);
				$pdf->SetFillColor(255,255,255);
	        	$pdf->MultiCell($width, 6, $object->opp_percent.'%', '0', 'C');
	        }
			else
			{
				$pdf->SetFont('','', $default_font_size - 2);
	        	$pdf->SetXY($this->marge_gauche+$width+2,$RoundedRectHeight+6);
	        	$pdf->SetTextColorArray($textcolor);
				$pdf->SetFillColor(255,255,255);
				$pdf->MultiCell($width, 6, '', '0', 'C');
			}

	        // Commercial Interlocutor
			$pdf->SetAlpha($opacity);
			$pdf->RoundedRect($this->marge_gauche+$width*2+4, $RoundedRectHeight, $width, 6, $roundradius, $round_corner = '1001', 'FD', $this->border_style, $bgcolor);
			$pdf->SetAlpha(1);
	        $pdf->SetFont('','B', $default_font_size - 2);
	        $pdf->SetXY($this->marge_gauche+$width*2+4,$RoundedRectHeight+0.5);
	        $pdf->SetTextColorArray($textcolor);
			$text='<div style="line-height:90%;">'.$outputlangs->transnoentities("TypeContact_project_internal_PROJECTLEADER").'</div>';
	        $pdf->writeHTMLCell($width, 5,$this->marge_gauche+$width*2+4,$RoundedRectHeight+0.5, $text, 0, 0, false, true, 'C', true);
	        
	        $contact_id = $object->getIdContact('internal','PROJECTLEADER');

	        if (! empty($contact_id))
	        {
	        	$object->fetch_user($contact_id[0]);
	        	$pdf->SetFont('','', $default_font_size - 2);
	        	$pdf->SetXY($this->marge_gauche+$width*2+4,$RoundedRectHeight+6);
	        	$pdf->SetTextColorArray($textcolor);
	        	$pdf->MultiCell($width, 5, $object->user->firstname.' '.$object->user->lastname, 0, 'C', false);
				$pdf->SetXY($this->marge_gauche+$width*2+4,$RoundedRectHeight+9);
	        	$pdf->SetTextColorArray($textcolor);
	        	$pdf->MultiCell($width, 7, $object->user->office_phone, '0', 'C');
	        }
	        else if ($object->user_author_id)
	        {
	        	$object->fetch_user($object->user_author_id);
	        	$pdf->SetFont('','', $default_font_size - 2);
	        	$pdf->SetXY($this->marge_gauche+$width*2+4,$RoundedRectHeight+6);
	        	$pdf->SetTextColorArray($textcolor);
	        	$pdf->MultiCell($width, 6, $object->user->firstname.' '.$object->user->lastname, '0', 'C');
				$pdf->SetXY($this->marge_gauche+$width*2+4,$RoundedRectHeight+9);
	        	$pdf->SetTextColorArray($textcolor);
	        	$pdf->MultiCell($width, 7, $object->user->office_phone, '0', 'C');
	        }
			else
			{
				$pdf->SetFont('','', $default_font_size - 2);
	        	$pdf->SetXY($this->marge_gauche+$width*2+4,$RoundedRectHeight+6);
	        	$pdf->SetTextColorArray($textcolor);
				$pdf->SetFillColor(255,255,255);
				$pdf->MultiCell($width, 6, '', '0', 'C');
			}

	        // Customer code
			$pdf->SetAlpha($opacity);
			$pdf->RoundedRect($this->marge_gauche+$width*3+6, $RoundedRectHeight, $width, 6, $roundradius, $round_corner = '1001', 'FD', $this->border_style, $bgcolor);
			$pdf->SetAlpha(1);
	        $pdf->SetFont('','B', $default_font_size - 2);
	        $pdf->SetXY($this->marge_gauche+$width*3+6,$RoundedRectHeight);
	        $pdf->SetTextColorArray($textcolor);
	        $pdf->MultiCell($width, 5, $outputlangs->transnoentities("CustomerCode"), 0, 'C', false);

	        if ($object->thirdparty->code_client)
	        {
	        	$pdf->SetFont('','', $default_font_size - 2);
	        	$pdf->SetXY($this->marge_gauche+$width*3+6,$RoundedRectHeight+6);
	        	$pdf->SetTextColorArray($textcolor);
	        	$pdf->MultiCell($width, 6, $outputlangs->transnoentities($object->thirdparty->code_client), '0', 'C');
	        }
			else
			{
				$pdf->SetFont('','', $default_font_size - 2);
	        	$pdf->SetXY($this->marge_gauche+$width*3+6,$RoundedRectHeight+6);
	        	$pdf->SetTextColorArray($textcolor);
				$pdf->SetFillColor(255,255,255);
				$pdf->MultiCell($width, 6, '', '0', 'C');
			}
			
			// Opportunity Amount
			$pdf->SetAlpha($opacity);
			$pdf->RoundedRect($this->marge_gauche+$width*4+8, $RoundedRectHeight, $width, 6, $roundradius, $round_corner = '1001', 'FD', $this->border_style, $bgcolor);
			$pdf->SetAlpha(1);
	        $pdf->SetFont('','B', $default_font_size - 2);
	        $pdf->SetXY($this->marge_gauche+$width*4+8,$RoundedRectHeight);
	        $pdf->SetTextColorArray($textcolor);
	        $pdf->MultiCell($width, 5, $outputlangs->transnoentities("OpportunityAmount"), 0, 'C', false);
	        
			if ($object->budget_amount)
			{
	        $pdf->SetFont('','', $default_font_size - 2);
	        $pdf->SetXY($this->marge_gauche+$width*4+8,$RoundedRectHeight+6);
	        $pdf->SetTextColorArray($textcolor);
	        $pdf->MultiCell($width, 6, $cursymbolbefore.price($object->budget_amount, 0, $outputlangs).$cursymbolafter, '0', 'C');	
			}
		
		$pdf->SetTextColorArray($textcolor);
		}
	}

	/**
	 *   	Show footer of page. Need this->emetteur object
     *
	 *   	@param	PDF			&$pdf     			PDF
	 * 		@param	Object		$object				Object to show
	 *      @param	Translate	$outputlangs		Object lang for output
	 *      @param	int			$hidefreetext		1=Hide free text
	 *      @return	void
	 */
	function _pagefoot(&$pdf,$object,$outputlangs,$hidefreetext=0)
	{
		global $conf;
		$showdetails=$conf->global->MAIN_GENERATE_DOCUMENTS_SHOW_FOOT_DETAILS;
		return pdf_ultimatepagefoot($pdf,$outputlangs,'PROJECT_FREE_TEXT', $this->emetteur, $this->marge_basse, $this->marge_gauche, $this->page_hauteur, $object, $showdetails, $hidefreetext, $footertextcolor);
	}
	
	/**
	 *   	Define Array Column Field
	 *
	 *   	@param	object			$object    		common object
	 *   	@param	Translate		$outputlangs    langs
	 *      @param	int				$hidedetails	Do not show line details
	 *      @param	int				$hidedesc		Do not show desc
	 *      @param	int				$hideref		Do not show ref
	 *      @return	null
	 */
    public function defineColumnField($object,$outputlangs,$hidedetails=0,$hidedesc=0,$hideref=0)
    {
	    global $conf, $hookmanager;

	    // Default field style for content
	    $this->defaultContentsFieldsStyle = array(
	        'align' => 'R', // R,C,L
	        'padding' => array(0.5,0.5,0.5,0.5), // Like css 0 => top , 1 => right, 2 => bottom, 3 => left
	    );

	    // Default field style for content
	    $this->defaultTitlesFieldsStyle = array(
	        'align' => 'C', // R,C,L
	        'padding' => array(0.5,0,0.5,0), // Like css 0 => top , 1 => right, 2 => bottom, 3 => left
	    );

	    /*
	     * For exemple
	     $this->cols['theColKey'] = array(
	     'rank' => $rank, // int : use for ordering columns
	     'width' => 20, // the column width in mm
	     'title' => array(
	     'textkey' => 'yourLangKey', // if there is no label, yourLangKey will be translated to replace label
	     'label' => ' ', // the final label : used fore final generated text
	     'align' => 'L', // text alignement :  R,C,L
	     'padding' => array(0.5,0.5,0.5,0.5), // Like css 0 => top , 1 => right, 2 => bottom, 3 => left
	     ),
	     'content' => array(
	     'align' => 'L', // text alignement :  R,C,L
	     'padding' => array(0.5,0.5,0.5,0.5), // Like css 0 => top , 1 => right, 2 => bottom, 3 => left
	     ),
	     );
	     */
		 
		  $rank=0; // do not use negative rank
		  $this->cols['num'] = array(
	        'rank' => $rank,
	        'width' => (empty($conf->global->ULTIMATE_DOCUMENTS_WITH_NUMBERING_WIDTH)?10:$conf->global->ULTIMATE_DOCUMENTS_WITH_NUMBERING_WIDTH), // in mm 
	        'status' => false,
	        'title' => array(
	            'textkey' => 'Numbering', // use lang key is usefull in somme case with module
	            'align' => 'C',
	            // 'textkey' => 'yourLangKey', // if there is no label, yourLangKey will be translated to replace label
	            // 'label' => ' ', // the final label
	            'padding' => array(0.5,0.5,0.5,0.5), // Like css 0 => top , 1 => right, 2 => bottom, 3 => left
	        ),
	        'content' => array(
	            'align' => 'C',
	        ),
	    );
		if (!empty($conf->global->ULTIMATE_PROJECT_WITH_LINE_NUMBER))
	    {
	        $this->cols['num']['status'] = true;
	    }
		 
		  $rank = $rank + 10; // do not use negative rank
		  $this->cols['ref'] = array(
	        'rank' => $rank,
	        'width' => 22, // in mm 
	        'status' => true,
	        'title' => array(
	            'textkey' => 'RefTask', // use lang key is usefull in somme case with module
	            'align' => 'C',
	            // 'textkey' => 'yourLangKey', // if there is no label, yourLangKey will be translated to replace label
	            // 'label' => ' ', // the final label
	            'padding' => array(0.5,0.5,0.5,0.5), // Like css 0 => top , 1 => right, 2 => bottom, 3 => left
	        ),
	        'content' => array(
	            'align' => 'L',
	        ),
		   'border-left' => false, // remove left line separator
	    );
		
		if (!empty($conf->global->ULTIMATE_PROJECT_WITH_LINE_NUMBER))
	    {
	        $this->cols['ref']['border-left'] = true;
	    }

	    $rank = $rank + 10; // do not use negative rank
	    $this->cols['desc'] = array(
	        'rank' => $rank,
	        'width' => false, // only for desc
	        'status' => true,
	        'title' => array(
	            'textkey' => 'Designation', // use lang key is usefull in somme case with module
	            'align' => 'L',
	            // 'textkey' => 'yourLangKey', // if there is no label, yourLangKey will be translated to replace label
	            // 'label' => ' ', // the final label
	            'padding' => array(0.5,0.5,0.5,0.5), // Like css 0 => top , 1 => right, 2 => bottom, 3 => left
	        ),
	        'content' => array(
	            'align' => 'L',
	        ),
			'border-left' => true, // remove left line separator
	    );
		
		$rank = $rank + 10;
		$this->cols['planned_workload'] = array(
			'rank' => $rank,
			'status' => true,
			'width' => 20, // in mm  
			'title' => array(
				'textkey' => 'PlannedWorkloadShort'
			),
			'content' => array(
	            'align' => 'R',
	        ),
			'border-left' => true, // add left line separator
		);

	   $rank = $rank + 10;
	    $this->cols['progress'] = array(
	        'rank' => $rank,
	        'width' => 20, // in mm 
	        'status' => false,
	        'title' => array(
	            'textkey' => 'Progress'
	        ),
			'content' => array(
	            'align' => 'R',
	        ),
	        'border-left' => true, // add left line separator
	    );
		
		if(!empty($conf->global->ULTIMATE_GENERATE_PROJECTS_WITH_PROGRESS))
	    {
	        $this->cols['progress']['status'] = true;
	    }
		
		$rank = $rank + 10;
	    $this->cols['datestart'] = array(
	        'rank' => $rank,
	        'width' => 20, // in mm
	        'status' => false,
	        'title' => array(
	            'textkey' => 'DateStart'
	        ),
			'content' => array(
	            'align' => 'R',
	        ),
	        'border-left' => true, // add left line separator
	    );
		
	    if (!empty($conf->global->ULTIMATE_GENERATE_PROJECTS_WITH_DATESTART))
		{
	        $this->cols['datestart']['status'] = true;
	    }
		
		$rank = $rank + 10;
	    $this->cols['dateend'] = array(
	        'rank' => $rank,
	        'width' => 20, // in mm 
	        'status' => false,
	        'title' => array(
	            'textkey' => 'DateEnd'
	        ),
			'content' => array(
	            'align' => 'R',
	        ),
	        'border-left' => true, // add left line separator
	    );
		
		if (!empty($conf->global->ULTIMATE_GENERATE_PROJECTS_WITH_DATEEND))
		{
	        $this->cols['dateend']['status'] = true;
	    }
		
		$parameters=array(
	        'object' => $object,
	        'outputlangs' => $outputlangs,
	        'hidedetails' => $hidedetails,
	        'hidedesc' => $hidedesc,
	        'hideref' => $hideref
	    );
		
		$reshook=$hookmanager->executeHooks('defineColumnField',$parameters,$this);    // Note that $object may have been modified by hook
	    if ($reshook < 0)
	    {
	        setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
	    }
	    elseif (empty($reshook))
	    {
	        $this->cols = array_replace($this->cols, $hookmanager->resArray); // array_replace is used to preserve keys
	    }
	    else
	    {
	        $this->cols = $hookmanager->resArray;
	    }
	}

}

?>