/*
    Dr Warehouse is a document oriented data warehouse for clinicians. 
    Copyright (C) 2017  Nicolas Garcelon

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

    Contact : Nicolas Garcelon - nicolas.garcelon@institutimagine.org
    Institut Imagine
    24 boulevard du Montparnasse
    75015 Paris
    France
*/
	 	function startIntro(){
	        	if (!document.getElementById('id_select_thesaurus_data_2')) {
		        	add_atomic_query('code');
		        }
	        	var intro = introJs();
		          intro.setOptions({
		            steps: [
		              {
		                element: document.querySelector('#id_div_filtre_texte_1'),
		                intro: "<div style='width:400px'>Ceci est une requ�te atomique. Vous pouvez faire plusieurs requ�tes atomiques en ajoutant des requ�tes full text ou structur�es. <br>Une requ�te atomique de type full text va rechercher les termes au sein du m�me compte rendu. <br>Pour rechercher 2 termes qui ne sont pas forc�ment dans le m�me compte rendu il faut les mettre dans 2 requ�tes atomiques diff�rents. Le moteur se chargera de faire l'intersection !</div>",
		                position: 'right'
		              },
		              {
		                element: document.querySelectorAll('#id_input_filtre_texte_1')[0],
		                intro: "<div style='width:300px'>Le moteur va retrouver exactement l'expression recherch�e.<br>Exemple de requ�te en texte libre : <br>(diabet% or affection% endocrinienne%) and lupus not diabete gestationnel<br><br>vous pouvez utiliser les op�tareurs or, and, not et lejocker % pour la fin d'un mot</div>",
		                position: 'right'
		              },
		              {
		                element: '#id_span_nbresult_atomique_1',
		                intro: "<div style='width:300px'>Appuyez sur entr�e dans le texte � gauche ou cliquez ici pour avoir le nombre de patients retrouv�s.</div>",
		                position: 'right'
		              },
		              {
		                element: '#id_div_filtre_texte_avance_1',
		                intro: "<div style='width:300px'>Vous pouvez davantage sp�cifier votre recherche en cliquant sur la recherche avanc�e. Comme l'�ge du patient � la date du document, le service, etc...</div>",
		                position: 'right'
		              },
		              {
		                element: document.querySelector('#id_div_filtre_texte_2'),
		                intro: "<div style='width:300px'>Une recherche atomatique de type structur� permet de rechercher dans les donn�es cod�es. Par exemple, on peut recherche des patients qui ont une cr�atinin�mie > 100</div>",
		                position: 'right'
		              },
		              {
		                element: document.querySelector('#id_select_thesaurus_data_2'),
		                intro: "<div style='width:300px'>Il faut pr�ciser le thesaurus</div>",
		                position: 'right'
		              },
		              {
		                element: document.querySelectorAll('#id_rechercher_code_2')[0],
		                intro: "<div style='width:300px'>Il faut rechercher l'examen biologique, par exemple 'creat sang'. </div>",
		                position: 'right'
		              },
		              {
		                element: '#id_div_resultat_recherche_code_2',
		                intro: "<div style='width:300px'>>Vous pouvez d�plier le thesaurus pour afficher les examens trouv�s.<br>Puis s�lectionnez l'examen qui vous int�resse, par exemple la Cr�atinine (�mol/l) S�rum/Plasma Enzymologie </div>",
		                position: 'right'
		              },
		              {
		                element: '#id_div_selection_code_2',
		                intro: "<div style='width:300px'>Vous pouvez pr�ciser l'un des 5 crit�res de filtre pour la cr�at: hors borne, ou sup�rieur / inf�rieur � une valeur, ou dans un interval de valeur, ou n fois sup�rieur / inf�rieur � la borne </div>",
		                position: 'right'
		              },
		              {
		                element: '#id_div_formulaire_patient',
		                intro: "<div style='width:300px'>Dans le filtre 'patient' vous pouvez ajouter un filtre plus g�n�ral sur les patients aujourd'hui.</div>",
		                position: 'right'
		              },
		              {
		                element: '#id_bouton_submit_moteur',
		                intro: "<div style='width:300px'>Cliquez ici pour afficher les patients retrouv�s.</div>",
		                position: 'right'
		              }
		            ]
		          });
		          
		          intro.onchange(function(targetElement) {
		          	if (targetElement.id=='id_div_filtre_texte_avance_1') {
		          		//$("#").click();
	        			deplier('id_div_filtre_texte_avance_1','block');
		          	}
		          	if (targetElement.id=='id_div_formulaire_patient') {
	        			deplier('id_div_formulaire_patient','block');
		          	}
		          	if (targetElement.id=='id_div_resultat_recherche_code_2') {
	        			simuler_recherche_structure();
		          		rechercher_code_sous_thesaurus (2,92688519,'');
		          	}
		          	if (targetElement.id=='id_div_selection_code_2') {
		          		ajouter_formulaire_code(2,92688554);
		          	}
		          	
		          	
		          });
		
		          intro.start();
	      }
	      
	      function simuler_recherche_structure () {
	        	if (document.getElementById('id_rechercher_code_2')) {
	        		document.getElementById('id_select_thesaurus_data_2').value='STARE';
	        		document.getElementById('id_rechercher_code_2').value='creat sang';
	        		rechercher_code(2);
	        	}
	      	
	      }