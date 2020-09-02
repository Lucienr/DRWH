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

<div class="m-2" v-cloak id="testVue">
    <h1> Mes cohortes </h1>
    <input class="ml-2" type="text" v-model="search_cohorts" placeholder="Recherche.."/>
    <button class="btn btn-sm btn-primary" style="margin-left:10%;" data-toggle="collapse" data-target="#collapseCreateCohort" aria-expanded="false" aria-controls="collapseCreateCohort" > + </button>
    <button class="btn btn-sm btn-primary" data-toggle="collapse" data-target="#collapseFusion" aria-expanded="false" aria-controls="collapseFusion"> Fusion </button>
    <br>
    <!-- COHORT FUSION FORM -->
    <div class="collapse col-md-4" id="collapseFusion">
      <br>
      <form>
        <table>
					<tr>
            <td class="question_user">Titre nouvelle cohorte: </td>
            <td>
              <input v-model="cohort_merge_title" type="text" size="50"  name="title_cohort" id="id_fusionner_titre_cohorte" class="form">
            </td>
          </tr>
					<tr>
						<td class="question_user">Cohorte 1 : </td>
						<td>
              <select placeholder="Choisir une cohorte" v-model="cohort_merge_one">
                <option  v-for="cohort in cohorts" :value="cohort">{{cohort.title}}</option>
              </select>
            </td>
          </tr>

          <tr>
            <td class="question_user">Cohorte 2 : </td>
            <td>
              <select cplaceholder="Choisissez une cohorte" v-model="cohort_merge_two">
                <option v-for="cohort in cohorts" :value="cohort">{{cohort.title}}</option>
              </select>
            </td>
          </tr>
          <tr><td style="vertical-align:top;" class="question_user" > Description : </td><td>
            <textarea v-model="cohort_merge_desc" cols="50" rows="6" class="form"></textarea>
          </td></tr>
        </table>
      </form>
      <button class="btn btn-sm btn-primary" data-toggle="collapse" @click="mergeCohort()" data-target="#collapseFusion" aria-expanded="false" aria-controls="collapseFusion"> Créer </button>
    </div>
    <!-- DIV Créer une Cohorte -->
    <div class="collapse col-md-4" id="collapseCreateCohort">
      <br>
      <form>
        <table>
          <tr>
            <td class="question_user">
              Titre :
            </td>
            <td>
              <input v-model="new_cohort_title" type="text" size="50"  class="form">
            </td>
          </tr>
          <tr>
            <td class="question_user">Datamart concerné : </td>
            <td>
              <select v-model="new_cohort_datamart" placeholder="Choisissez un datamart">
                <option value='0'>Défaut></option>
              </select>
            </td>
            </tr>
          <tr><td style="vertical-align:top;" class="question_user" >Description : </td><td>
            <textarea v-model="new_cohort_description" cols="50" rows="6" class="form"></textarea>
          </td></tr>
        </table>
        <br>
      </form>
      <button class="btn btn-sm btn-primary" data-toggle="collapse" @click="createCohort()" data-target="#collapseCreateCohort" aria-expanded="false" aria-controls="collapseCreateCohort">Créer</button>
    </div>
    <!-- COHORT DETAILED VIEW -->
    <div class="row">
      <div class="col-md-4 pre-scrollable" style="max-height:600px;">
          <div v-for="cohort in filteredCohorts" class="row m-2">
              <div class="card clickable col-md-12" @click="showCohortResult(cohort)">
                  <div class="card-body">
                      {{cohort.title}}<br>
                      <small>
                          Créé le {{cohort.created_date}}, {{cohort.nb_patients}} patients
                      </small>
                  </div>
              </div>
          </div>
      </div>
      <div v-if="selectedCohort" class="col-md-8">
        <h5 class="text-center">{{selectedCohort.title}}</h5>
        <button class="btn btn-sm btn-primary" @click="deleteCohort(selectedCohort)"> delete </button>
        <br>
        <br>
        <ul class="nav nav-tabs" id="myTab" role="tablist">
          <li class="nav-item">
            <a class="nav-link active" id="desc-patient-tab" data-toggle="tab" href="#desc_pat" role="tab" aria-controls="desc_pat" aria-selected="true">Description</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="incl-patient-tab" data-toggle="tab" href="#incl_pat" role="tab" aria-controls="incl_pat" aria-selected="true">Patients inclus  <font color="red">{{filteredPatientsIncluded.length}}</font></a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="excl-patient-tab" data-toggle="tab" href="#excl_pat" role="tab" aria-controls="excl_pat" aria-selected="false">Patients exclus  <font color="red">{{filteredPatientsExcluded.length}}</font></a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="doubt-patient-tab" data-toggle="tab" href="#doubt_pat" role="tab" aria-controls="doubt_pat" aria-selected="false">Patients doute  <font color="red">{{filteredPatientsDoubt.length}}</font></a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="import-patient-tab" data-toggle="tab" href="#import_pat" role="tab" aria-controls="import_pat" aria-selected="false">Importer des patients</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="comment-patient-tab" data-toggle="tab" href="#comment_pat" role="tab" aria-controls="comment_pat" aria-selected="false">Commentaires patients</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="concept-patient-tab" data-toggle="tab" href="#concept_pat" role="tab" aria-controls="concept_pat" aria-selected="false">Concepts patient</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="similar-patient-tab" data-toggle="tab" href="#similar_pat" role="tab" aria-controls="similar_pat" aria-selected="false">Patients similaires</a>
          </li>
        </ul>
        <div class="tab-content" id="myTabContent">
          <!-- DIV Description  -->
          <div class="tab-pane fade show active" id="desc_pat" role="tabpanel" aria-labelledby="desc-patient-tab">
            <h5>Description de la cohorte</h5>
            {{selectedCohort.description_cohort}}
            <h5>Gestion des utilisateurs</h5>
            <h5>Les requêtes de la cohorte</h5>
          </div>
          <!-- DIV Patient inclus -->
          <div class="tab-pane fade row" id="incl_pat" role="tabpanel" aria-labelledby="incl-patient-tab">
            <table class="mt-2 table table-sm">
              <tbody style="font-size:13px;">
                <tr v-for="cohort_incl in filteredPatientsIncluded">
                    <td>
                      {{cohort_incl.patient.firstname}} {{cohort_incl.patient.lastname}} {{cohort_incl.patient.birth_date}} {{cohort_incl.patient.sex}}
                      <button type="button" class="btn btn-sm btn-primary">exclure</button>
                      <button type="button" class="btn btn-sm btn-primary">doute</button>
                    </td>
                </tr>
              </tbody>
            </table>
          </div>
          <!-- DIV patients exclus -->
          <div class="tab-pane fade" id="excl_pat" role="tabpanel" aria-labelledby="excl-patient-tab">
            <table class="mt-2 table table-sm">
              <tbody style="font-size:13px;">
                <tr v-for="cohort_ex in filteredPatientsExcluded">
                  <td>
                    {{cohort_ex.patient.firstname}} {{cohort_ex.patient.lastname}} {{cohort_ex.patient.birth_date}} {{cohort_ex.patient.sex}}

                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <!-- DIV Patient doute -->
          <div class="tab-pane fade" id="doubt_pat" role="tabpanel" aria-labelledby="doubt-patient-tab">
            <table class="mt-2 table table-sm">
              <tbody style="font-size:13px;">
                <tr v-for="cohort_dbt in filteredPatientsDoubt">
                  <td>{{cohort_dbt.patient.firstname}} {{cohort_dbt.patient.lastname}} {{cohort_dbt.patient.birth_date}} {{cohort_dbt.patient.sex}}</td>
              </tbody>
            </table>
          </div>
          <!-- DIV import patient -->
          <div class="tab-pane fade" id="import_pat" role="tabpanel" aria-labelledby="import-patient-tab">...</div>
          <!-- DIV Commentaires patient -->
          <div class="tab-pane fade" id="comment_pat" role="tabpanel" aria-labelledby="comment-patient-tab">...</div>
          <!-- DIV Concepts patient -->
          <div class="tab-pane fade" id="concept_pat" role="tabpanel" aria-labelledby="concept-patient-tab">...</div>
          <!-- DIV Patients similaires -->
          <div class="tab-pane fade" id="similar_pat" role="tabpanel" aria-labelledby="similar-patient-tab">...</div>

        </div>

      </div>
    </div>
  </div>
<script type="module" src="cohorts.js"></script>

<? include "foot.php"; ?>
