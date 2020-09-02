<?php
include "head.php";
include "menu.php";
session_write_close();
?>
<link rel="stylesheet" href="vue/bootstrap.css">
<link rel="stylesheet" href="fontawesome/css/all.css">
<script type="module" src="APIService.js"></script>
<script src="vue/axios.min.js"></script>
<script src="vue/vue.js"></script>
<script src="vue/moment.min.js"></script>
<script src="vue/bootstrap.min.js"></script>

<div class="col-md-4" v-cloak id="testVue">
    <h1> Rechercher des patients </h1>
		<h3> Sur tout l'entrepot </h3>
    <div class="row">
			<div class="container">
				<h2 style="cursor:pointer;" @click="addAtomicQueryFullText()">+ Ajouter un filtre Full text</h2>
					<div class="col-md-12 m-2" v-for="(text_search, index) in atomic_queries_fulltext" :key="index">
						<br>
						<!-- <div class="card col-md-12">
							<div class="card-body input-group mb-3">
                <div class="input-group-append">
                  <button type="button" @click="removeAtomicQueryFulltext(index)" class="btn btn-block btn-danger">x</button>
                </div>
								<textarea v-model="text_search.searchText" class="form-control"></textarea>
								<br>
							</div>
						</div> -->
            <span class="border border-dark">
              <form class="form">
                <div class="float-right circle">{{index}}</div>
                <div class="form-group">
                  <button type="button" @click="removeAtomicQueryFulltext(index)" class="float-right btn-danger btn-xs">x</button>
                  <textarea v-model="text_search.searchText" class="form-control"></textarea>
                </div>
                <div class="form-row">
                  <div class="form-check">
                    <input v-model="text_search.synonym" true-value="1" false-value="0" class="form-check-input" type="checkbox" id="CheckSynonym">
                    <label class="form-check-label" for="CheckSynonym">
                      Etendre aux synonymes
                    </label>
                    <br>

                    <label class="form-check-label" for="context">
                      Context
                    </label>
                    <select v-model="text_search.context" class="form-select" id="context">
                      <option value="patient_text">patient</option>
                      <option value="family_text">famille</option>
                      <option value="text">text</option>
                    </select>

                    <br>
                    <label class="form-check-label" for="certainty">
                      Negations
                    </label>
                    <select v-model="text_search.certainty" class="form-select" id="certainty">
                      <option value="1">inclu</option>
                      <option value="0">exclu</option>
                    </select>
                    <h2 style="cursor:pointer;" data-toggle="collapse" data-target="#collapseAdvancedFitlers" aria-expanded="false" aria-controls="collapseAdvancedFitlers">+ Ajouter un filtre avancé</h2>
                    <div class="column collapse" id="collapseAdvancedFitlers">
                      <table border="0">
                        <tbody>
                          <tr>
                            <td rowspan="2" style="vertical-align:top">Age du patient à la date du document :</td>
                            <td>
                              de
                              <input type="text" id="id_input_filtre_age_deb_document_1" name="age_deb_document_1" class="filtre_date_document" size="2" value="">
                              ans à
                              <input type="text" id="id_input_filtre_age_fin_document_1" name="age_fin_document_1" class="filtre_date_document" size="2" value="" onblur="verif_chaine_modifiee_avant_calcul(1,'id_input_filtre_age_fin_document_1','');">
                              ans.
                            </td>
                          </tr>
                          <tr>
                            <td>
                              de
                              <input type="text" id="id_input_filtre_agemois_deb_document_1" name="agemois_deb_document_1" class="filtre_date_document" size="2" value="">
                              mois à
                              <input type="text" id="id_input_filtre_agemois_fin_document_1" name="agemois_fin_document_1" class="filtre_date_document" size="2" value="" onblur="verif_chaine_modifiee_avant_calcul(1,'id_input_filtre_agemois_fin_document_1','');">
                              mois
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </form>
            </span>
					</div>
					<br>
          <button type="submit" @click="sumbitAtomicQuery()" class="btn btn-block btn-primary col-sm-5">Submit</button>
				</div>
        <div class="col-md-8 pre-scrollable" style="max-height:600px;">
            <div v-for="document in searched_document_list" class="row m-2">
                <div class="card clickable col-md-12">
                    <div class="card-body">
                        <strong>{{document.title}} du {{document.document_date}} par {{document.author}}</strong>
                        <br>
                        <span v-html="document.displayed_text"></span>
                    </div>
                </div>
            </div>
        </div>
			</div>


    </div>
  </div>
<script type="module" src="search_engine.js"></script>

<? include "foot.php"; ?>
