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
		                intro: "<div style='width:400px'>Ceci est une requête atomique. Vous pouvez faire plusieurs requêtes atomiques en ajoutant des requêtes full text ou structurées. <br>Une requête atomique de type full text va rechercher les termes au sein du même compte rendu. <br>Pour rechercher 2 termes qui ne sont pas forcément dans le même compte rendu il faut les mettre dans 2 requêtes atomiques différents. Le moteur se chargera de faire l'intersection !</div>",
		                position: 'right'
		              },
		              {
		                element: document.querySelectorAll('#id_input_filtre_texte_1')[0],
		                intro: "<div style='width:300px'>Le moteur va retrouver exactement l'expression recherchée.<br>Exemple de requête en texte libre : <br>(diabet% or affection% endocrinienne%) and lupus not diabete gestationnel<br><br>vous pouvez utiliser les opétareurs or, and, not et lejocker % pour la fin d'un mot</div>",
		                position: 'right'
		              },
		              {
		                element: '#id_span_nbresult_atomique_1',
		                intro: "<div style='width:300px'>Appuyez sur entrée dans le texte à gauche ou cliquez ici pour avoir le nombre de patients retrouvés.</div>",
		                position: 'right'
		              },
		              {
		                element: '#id_div_filtre_texte_avance_1',
		                intro: "<div style='width:300px'>Vous pouvez davantage spécifier votre recherche en cliquant sur la recherche avancée. Comme l'âge du patient à la date du document, le service, etc...</div>",
		                position: 'right'
		              },
		              {
		                element: document.querySelector('#id_div_filtre_texte_2'),
		                intro: "<div style='width:300px'>Une recherche atomatique de type structuré permet de rechercher dans les données codées. Par exemple, on peut recherche des patients qui ont une créatininémie > 100</div>",
		                position: 'right'
		              },
		              {
		                element: document.querySelector('#id_select_thesaurus_data_2'),
		                intro: "<div style='width:300px'>Il faut préciser le thesaurus</div>",
		                position: 'right'
		              },
		              {
		                element: document.querySelectorAll('#id_rechercher_code_2')[0],
		                intro: "<div style='width:300px'>Il faut rechercher l'examen biologique, par exemple 'creat sang'. </div>",
		                position: 'right'
		              },
		              {
		                element: '#id_div_resultat_recherche_code_2',
		                intro: "<div style='width:300px'>>Vous pouvez déplier le thesaurus pour afficher les examens trouvés.<br>Puis sélectionnez l'examen qui vous intéresse, par exemple la Créatinine (µmol/l) Sérum/Plasma Enzymologie </div>",
		                position: 'right'
		              },
		              {
		                element: '#id_div_selection_code_2',
		                intro: "<div style='width:300px'>Vous pouvez préciser l'un des 5 critères de filtre pour la créat: hors borne, ou supérieur / inférieur à une valeur, ou dans un interval de valeur, ou n fois supérieur / inférieur à la borne </div>",
		                position: 'right'
		              },
		              {
		                element: '#id_div_formulaire_patient',
		                intro: "<div style='width:300px'>Dans le filtre 'patient' vous pouvez ajouter un filtre plus général sur les patients aujourd'hui.</div>",
		                position: 'right'
		              },
		              {
		                element: '#id_bouton_submit_moteur',
		                intro: "<div style='width:300px'>Cliquez ici pour afficher les patients retrouvés.</div>",
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