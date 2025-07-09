<?php
################################################################################
# @Name : wysiwyg.php
# @Description : text editor
# @Call : /ticket.php
# @Parameters : 
# @Author : Flox
# @Create : 06/03/2013
# @Update : 31/08/2023
# @Version : 3.2.38
################################################################################

//initialize variables
if(!isset($rright['ticket_description'])) {$rright['ticket_description'] = '';}
if(!isset($rright['ticket_thread_add'])) {$rright['ticket_thread_add'] = '';}
if(!isset($rright['ticket_description_insert_image'])) {$rright['ticket_description_insert_image'] = '';}
if(!isset($rright['procedure'])) {$rright['procedure'] = '';}
if(!isset($rright['admin'])) {$rright['admin'] = '';}
?>
<script type="text/javascript" src="./vendor/components/jquery-hotkeys/jquery.hotkeys.js"></script>
<script type="text/javascript" src="./vendor/components/bootstrap-wysiwyg/js/bootstrap-wysiwyg.min.js"></script>
<script type="text/javascript">
	//load text from editor to input value
	function loadVal(){
		<?php
		if($_GET['page']=='ticket') {
			if ($rright['ticket_description']!=0 || $_GET['action']=='new')
			{
				echo '
				var editor=document.getElementById("editor");
				if(editor != null)
				{
					text = $("#editor").html();
					document.myform.text.value = text;
				}
				';
			}
		}
		
		if($_GET['page']=='ticket' && $rright['ticket_thread_add'])
		{
			echo '
			var editor2=document.getElementById("editor2");
			if(editor2 != null)
			{

				text2 = $("#editor2").html();
				document.myform.text2.value = text2;
			}
			';
		}
		if($_GET['page']=='procedure' && $rright['procedure'])
		{
			echo '
			var editor=document.getElementById("editor");
			if(editor != null)
			{
				text = $("#editor").html();
				document.myform.text.value = text;
			}
			';
		}
		
		if($_GET['subpage']=='parameters' && $rright['admin'])
		{
			echo '
			text = $("#editor").html();
			document.general_form.text.value = text;
			text2 = $("#editor2").html();
			document.general_form.text2.value = text2;
			text3 = $("#editor3").html();
			document.general_form.text3.value = text3;
			';
		}
		?>
	}
	
jQuery(function($) {
	$('#editor').aceWysiwyg({
		toolbarStyle: 2,
		toolbar:
		[
			<?php
				//display a light font function if mobile is detected
				if(!$mobile)
				{
					echo '
						{
							name:\'font\',
							title:\''.T_('Police').'\',
							values:[\'Open Sans\',\'Special\',\'Arial\',\'Verdana\',\'Comic Sans MS\']
						},
						null,
						{
							name:\'fontSize\',
							title:\''.T_('Taille').'\',
							values:{1 : \''.T_('Taille 1').'\' , 2 : \''.T_('Taille 2').'\' , 3 : \''.T_('Taille 3').'\' , 4 : \''.T_('Taille 4').'\' , 5 : \''.T_('Taille 5').'\'} 
						},
						null,
						{name:\'bold\', title:\''.T_('Gras').'\'},
						{name:\'italic\', title:\''.T_('Italique').'\'},
						{name:\'underline\', title:\''.T_('Souligner').'\'},
						null,
						{name:\'insertunorderedlist\', title:\''.T_('Liste à puces').'\'},
						{name:\'insertorderedlist\', title:\''. T_('Liste numéroté').'\'},
						{name:\'outdent\', title:\''.T_('Diminuer le retrait').'\'},
						{name:\'indent\', title:\''.T_('Augmenter le retrait').'\'},
						null,
						{name:\'justifyleft\',title:\''.T_('Aligner à gauche').'\'},
						{name:\'justifycenter\',title:\''.T_('Centrer').'\'},
						{name:\'justifyright\',title:\''.T_('Aligner à droite').'\'},
						{name:\'justifyfull\',title:\''.T_('Justifier').'\'},
						null,
						{
							id:\'createLink\',
							name:\'createLink\',
							title:\''.T_('Insérer un lien').'\',
							placeholder:\''.T_('Insérer un lien').'\',
							button_text:\''.T_('Ajouter').'\'
						},
						null,
						';
							if($rright['ticket_description_insert_image']!=0)
							{
								echo '
								{
									name:\'insertImage\',
									title:\''.T_('Sélectionner une image').'\',
									placeholder:\'\',
									button_class:\'btn-inverse\',
									//choose_file:false,//hide choose file button
									button_text:\''.T_('Sélectionner une image').'\',
									button_insert_class:\'btn-pink\',
									button_insert:\''.T_('Insérer une image').'\'
								},
								null,
								';
							}
						echo '
						{name:\'foreColor\',title:\''.T_('Couleur').'\',values:[\'red\',\'green\',\'blue\',\'orange\',\'black\'],},
						null,
						{name:\'undo\',title:\''.T_('Annuler la modification').'\'},
						{name:\'redo\',title:\''.T_('Rétablir').'\'}
					';
				} else {
					echo '
						{
							name:\'font\',
							title:\''.T_('Police').'\',
							values:[\'Open Sans\',\'Special\',\'Arial\',\'Verdana\',\'Comic Sans MS\']
						},
						null,
						{
							name:\'fontSize\',
							title:\''.T_('Taille').'\',
							values:{1 : \''.T_('Taille 1').'\' , 2 : \''.T_('Taille 2').'\' , 3 : \''.T_('Taille 3').'\' , 4 : \''.T_('Taille 4').'\' , 5 : \''.T_('Taille 5').'\'} 
						},
						null,
						{name:\'bold\', title:\''.T_('Gras').'\'},
						{name:\'italic\', title:\''.T_('Italique').'\'},
						{name:\'underline\', title:\''.T_('Souligner').'\'},
					';
				}
			?>
		],
	});
	$('#editor2').aceWysiwyg({
		toolbarStyle: 2,
		toolbar:
		[
			<?php
				//display a light font function if mobile is detected
				if(!$mobile)
				{
					echo '
						{
							name:\'font\',
							title:\''.T_('Police').'\',
							values:[\'Open Sans\',\'Special\',\'Arial\',\'Verdana\',\'Comic Sans MS\']
						},
						null,
						{
							name:\'fontSize\',
							title:\''.T_('Taille').'\',
							values:{1 : \''.T_('Taille 1').'\' , 2 : \''.T_('Taille 2').'\' , 3 : \''.T_('Taille 3').'\' , 4 : \''.T_('Taille 4').'\' , 5 : \''.T_('Taille 5').'\'} 
						},
						null,
						{name:\'bold\', title:\''.T_('Gras').'\'},
						{name:\'italic\', title:\''.T_('Italique').'\'},
						{name:\'underline\', title:\''.T_('Souligner').'\'},
						null,
						{name:\'insertunorderedlist\', title:\''.T_('Liste à puces').'\'},
						{name:\'insertorderedlist\', title:\''. T_('Liste numéroté').'\'},
						{name:\'outdent\', title:\''.T_('Diminuer le retrait').'\'},
						{name:\'indent\', title:\''.T_('Augmenter le retrait').'\'},
						null,
						{name:\'justifyleft\',title:\''.T_('Aligner à gauche').'\'},
						{name:\'justifycenter\',title:\''.T_('Centrer').'\'},
						{name:\'justifyright\',title:\''.T_('Aligner à droite').'\'},
						{name:\'justifyfull\',title:\''.T_('Justifier').'\'},
						null,
						{
							name:\'createLink\',
							title:\''.T_('Insérer un lien').'\',
							placeholder:\''.T_('Insérer un lien').'\',
							button_text:\''.T_('Ajouter').'\'
						},
						null,
						';
							if($rright['ticket_description_insert_image']!=0)
							{
								echo '
								{
									name:\'insertImage\',
									title:\''.T_('Insérer une image').'\',
									placeholder:\'\',
									button_class:\'btn-inverse\',
									//choose_file:false,//hide choose file button
									button_text:\''.T_('Sélectionner une image').'\',
									button_insert_class:\'btn-pink\',
									button_insert:\''.T_('Insérer une image').'\'
								},
								null,
								';
							}
						echo '
						{name:\'foreColor\',title:\''.T_('Couleur').'\',values:[\'red\',\'green\',\'blue\',\'orange\',\'black\'],},
						null,
						{name:\'undo\',title:\''.T_('Annuler la modification').'\'},
						{name:\'redo\',title:\''.T_('Rétablir').'\'}
					';
				} else {
					echo '
						{
							name:\'font\',
							title:\''.T_('Police').'\',
							values:[\'Open Sans\',\'Special\',\'Arial\',\'Verdana\',\'Comic Sans MS\']
						},
						null,
						{
							name:\'fontSize\',
							title:\''.T_('Taille').'\',
							values:{1 : \''.T_('Taille 1').'\' , 2 : \''.T_('Taille 2').'\' , 3 : \''.T_('Taille 3').'\' , 4 : \''.T_('Taille 4').'\' , 5 : \''.T_('Taille 5').'\'}  
						},
						null,
						{name:\'bold\', title:\''.T_('Gras').'\'},
						{name:\'italic\', title:\''.T_('Italique').'\'},
						{name:\'underline\', title:\''.T_('Souligner').'\'},
					';
				}
			?>
		],
	});
	$('#editor3').aceWysiwyg({
		toolbarStyle: 2,
		toolbar:
		[
			<?php
				//display a light font function if mobile is detected
				if(!$mobile)
				{
					echo '
						{
							name:\'font\',
							title:\''.T_('Police').'\',
							values:[\'Open Sans\',\'Special\',\'Arial\',\'Verdana\',\'Comic Sans MS\']
						},
						null,
						{
							name:\'fontSize\',
							title:\''.T_('Taille').'\',
							values:{1 : \''.T_('Taille 1').'\' , 2 : \''.T_('Taille 2').'\' , 3 : \''.T_('Taille 3').'\' , 4 : \''.T_('Taille 4').'\' , 5 : \''.T_('Taille 5').'\'} 
						},
						null,
						{name:\'bold\', title:\''.T_('Gras').'\'},
						{name:\'italic\', title:\''.T_('Italique').'\'},
						{name:\'underline\', title:\''.T_('Souligner').'\'},
						null,
						{name:\'insertunorderedlist\', title:\''.T_('Liste à puces').'\'},
						{name:\'insertorderedlist\', title:\''. T_('Liste numéroté').'\'},
						{name:\'outdent\', title:\''.T_('Diminuer le retrait').'\'},
						{name:\'indent\', title:\''.T_('Augmenter le retrait').'\'},
						null,
						{name:\'justifyleft\',title:\''.T_('Aligner à gauche').'\'},
						{name:\'justifycenter\',title:\''.T_('Centrer').'\'},
						{name:\'justifyright\',title:\''.T_('Aligner à droite').'\'},
						{name:\'justifyfull\',title:\''.T_('Justifier').'\'},
						null,
						{
							name:\'createLink\',
							title:\''.T_('Insérer un lien').'\',
							placeholder:\''.T_('Insérer un lien').'\',
							button_text:\''.T_('Ajouter').'\'
						},
						null,
						';
							if($rright['ticket_description_insert_image']!=0)
							{
								echo '
								{
									name:\'insertImage\',
									title:\''.T_('Insérer une image').'\',
									placeholder:\'\',
									button_class:\'btn-inverse\',
									//choose_file:false,//hide choose file button
									button_text:\''.T_('Sélectionner une image').'\',
									button_insert_class:\'btn-pink\',
									button_insert:\''.T_('Insérer une image').'\'
								},
								null,
								';
							}
						echo '
						{name:\'foreColor\',title:\''.T_('Couleur').'\',values:[\'red\',\'green\',\'blue\',\'orange\',\'black\'],},
						null,
						{name:\'undo\',title:\''.T_('Annuler la modification').'\'},
						{name:\'redo\',title:\''.T_('Rétablir').'\'}
					';
				} else {
					echo '
						{
							name:\'font\',
							title:\''.T_('Police').'\',
							values:[\'Open Sans\',\'Special\',\'Arial\',\'Verdana\',\'Comic Sans MS\']
						},
						null,
						{
							name:\'fontSize\',
							title:\''.T_('Taille').'\',
							values:{1 : \''.T_('Taille 1').'\' , 2 : \''.T_('Taille 2').'\' , 3 : \''.T_('Taille 3').'\' , 4 : \''.T_('Taille 4').'\' , 5 : \''.T_('Taille 5').'\'}  
						},
						null,
						{name:\'bold\', title:\''.T_('Gras').'\'},
						{name:\'italic\', title:\''.T_('Italique').'\'},
						{name:\'underline\', title:\''.T_('Souligner').'\'},
					';
				}
			?>
		],
	});
});
</script>