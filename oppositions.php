<?php
include "head.php";
include "menu.php";
include "admin_menu.php";
?>
<link rel="stylesheet" href="vue/bootstrap.css">

<div v-cloak class="container m-2" id="oppositions">
    <h1>Monitorer les oppositions</h1>
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
        <div v-if="patient" class="m-3 row border" style="width:max-content;">
            <div class="m-3">
                <h2 class="mb-0 mt-0">{{patient.firstname}} {{patient.lastname}} - {{patient.master_ipp}} (<a :href="'patient.php?patient_num=' + patient.patient_num"><small>Accéder au dossier</small></a>) </h2> 
                Né(e) le {{patient.birth_date | moment}} <br>
                <div class="m-2">
                    <small><i> Tous les documents seront automatiquement supprimés de l'entrepôt </i></small> <br>
                    <input class="form" type="button" @click="registerPatientOpposition(patient)" value="Enregistrer l'opposition ">
                </div>
                <div class="m-2">
                    <small><b>Ce patient est inclus dans {{patientNotOpposedMaps.length}} protocoles de recherche </b></small> <br>
                    <div class="table-responsive" style="max-height:200px; max-width:500px">
                        <table v-if="patientNotOpposedMaps.length != 0" class="table table-sm table-striped" style="font-size:12px;">
                            <thead>
                                <th>IPP</th>
                                <th>Protocole</th>
                                <th>Inclus le</th>
                                <th>Inclus par</th>
                                <th> </th>
                            </thead>
                            <tbody>
                                <tr v-for="map in patientNotOpposedMaps">
                                    <td>{{map.patient.master_ipp}}</td>
                                    <td>{{map.title}}</td>
                                    <td>{{map.register_date | moment}}</td>
                                    <td>{{map.register_by | not_empty}}</td>
                                    <td>
                                        <button class="btn btn-sm btn-link" style="font-size:0.7rem;" @click="updateMap(map)"> Enregistrer l'opposition</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <br>
                    <small><b>Ce patient est opposé à {{patientOpposedMaps.length}} protocoles de recherche </b></small>
                    <div class="table-responsive" style="max-height:200px; max-width:500px">
                        <table v-if="patientOpposedMaps.length != 0" class="table table-sm table-striped" style="font-size:12px;">
                            <thead>
                                <th>IPP</th>
                                <th>Protocole</th>
                                <th>Date</th>
                                <th>Source</th>
                                <th> </th>
                            </thead>
                            <tbody>
                                <tr v-for="map in patientOpposedMaps">
                                    <td>{{map.patient.master_ipp}}</td>
                                    <td>{{map.title}}</td>
                                    <td>{{map.opposition_date | moment}}</td>
                                    <td>
                                        <div v-if="map.opposition_source === 'drwh'">{{map.register_by | user}}</div> 
                                        <div v-else>Plateforme patient</div> 
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-link" style="font-size:0.7rem;" @click="updateMap(map)"> Annuler l'opposition</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-3 col-md-6">
        <h2> Liste des patients opposés </h2>
        <input class="input small" type="text" v-model="searchOppositions" placeholder="Recherche.."/>
        <div class="table-responsive mt-1" style="max-height:200px; max-width:500px">
            <table class="table table-sm table-striped" style="font-size:12px;">
                <thead>
                    <th> IPP </th>
                    <th> Date </th>
                    <th> Source </th>
                    <th> Annuler </th>
                </thead>
                <tbody >
                    <tr v-for="(opposition, index) in filteredPatientOppositions">
                        <td> {{opposition.master_ipp}} </td>
                        <td> {{opposition.opposition_date | moment}} </td>
                        <td> {{opposition.opposition_source}} </td>
                        <td> <button class="btn btn-link btn-sm p-0" @click="cancelPatientOpposition(opposition, index)"> Annuler </button> </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3 col">
        <h2> Liste des patients opposés à un protocole </h2>
        <input class="input small" type="text" v-model="searchMaps" placeholder="Recherche.."/>
        <div class="mt-1 table-responsive" style="max-height:200px; max-width:500px">
            <table class="table table-sm table-striped" style="font-size:12px;">
                <thead>
                    <th>IPP</th>
                    <th>Protocole</th>
                    <th>Date</th>
                    <th>Source</th>
                    <th>Annuler</th>
                </thead>
                <tbody>
                    <tr v-for="map in filteredMaps">
                        <td>{{map.patient.master_ipp}}</td>
                        <td>{{map.title}}</td>
                        <td>{{map.opposition_date | moment}}</td>
                        <td>
                            <div v-if="map.opposition_source === 'drwh'">{{map.register_by | user}}</div> 
                            <div v-else>Plateforme patient</div> 
                        </td>
                        <td>
                            <button class="btn btn-sm btn-link p-0" @click="updateMap(map)">Annuler</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>    
</div>
<script type="module" src="oppositions.js"></script>
<? include "foot.php"; ?>