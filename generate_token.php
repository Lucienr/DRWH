<?php
include "head.php";
include "menu.php";
include "admin_menu.php";
?>
<link rel="stylesheet" href="vue/bootstrap.css">
<div v-cloak class="container m-2" id="generate_token">
  
  <h1>Gestion des accès à la plateforme d'information patient</h1>
  <div class="col-md-6 mt-3">
    <div>
      <h2> Rechercher un patient </h2>
      <small><b>Précisez l'IPP du patient : </b> </small>
      <input class="input small" type=text v-model="searchIpp"/> 
      <input class="form" type="button" @click="searchPatient()" value="Rechercher">
      <div v-if="notFound">
        <br>
        Aucun patient trouvé. Vérifiez l'IPP saisie.
      </div>
    </div>
    <div v-if="patient" style="width:max-content;">
      <div class="mt-3">
        <h2 class="mb-0">
        {{patient.firstname}} {{patient.lastname}} - {{patient.master_ipp}} (<a :href="'patient.php?patient_num=' + patient.patient_num"><small>Accéder au dossier</small></a>) </h2> 
        Né(e) le {{patient.birth_date | moment}} <br>
        <input class="form mt-2" type="button" @click="generateToken()" value="Générer un token"> <br> <br>
        {{patientTokens.length}} token(s) existant(s) pour ce patient
        <div v-if="patientTokens" class="table-responsive mt-1" style="max-height:200px; max-width:800px">
          <table class="table table-sm table-striped" style="font-size:12px;">
            <thead>
              <th> Token </th>
              <th> Généré le </th>
              <th> Statut </th>
            </thead>
            <tbody>
              <tr v-for="token in patientTokens">
                <td style="word-break:break-all;">{{token.token}}</td>
                <td>{{token.created_date | moment}}</td>
                <td>
                  <div v-if="token.revoked_date">
                  Révoqué le : {{token.revoked_date | moment}}
                  </div>
                  <div v-else>
                    <button class="btn btn-link btn-sm p-0" @click="revokeToken(token)"> Révoquer </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="mt-3 col-md-12">
        <h2> Liste des tokens générés </h2>
        <input class="input small" type="text" v-model="searchTokens" placeholder="Recherche.."/>
        <div class="table-responsive mt-1" style="max-height:800px;">
            <table class="table table-sm table-striped" style="font-size:12px;">
                <thead>
                    <th> Patient </th>
                    <th> Token </th>
                    <th> Généré le </th>
                    <th>  </th>
                </thead>
                <tbody >
                    <tr v-for="(token, index) in filteredTokens">
                        <td> {{token.patient_data.firstname}} {{token.patient_data.lastname}} - {{token.patient_data.master_ipp}} <br>
                        Né(e) le {{token.patient_data.birth_date | moment}}</td>
                        <td style="max-width:200px; word-break:break-all;"> {{token.token}} </td>
                        <td> {{token.created_date | moment}} </td>
                        <td>
                          <div v-if="token.revoked_date">
                            Révoqué le : {{token.revoked_date | moment}}
                          </div>
                          <div v-else>
                            <button class="btn btn-link btn-sm p-0" @click="revokeToken(token)"> Révoquer </button>
                          </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
<script type="module" src="generate_token.js"></script>
<? include "foot.php"; ?>
