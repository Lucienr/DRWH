<?php
include "head.php";
include "menu.php";
session_write_close();
?>

<link rel="stylesheet" href="vue/vue-good-table.min.css">
<link rel="stylesheet" href="vue/vue-multiselect.min.css">
<link rel="stylesheet" href="vue/style.css">
<link rel="stylesheet" href="vue/bootstrap.css">

<script src="vue/vue-multiselect.min.js"></script>
<script src="vue/vue-good-table.min.js"></script>

<div class="m-2" v-cloak id="protocols">
    <h1> Gestion des protocoles de recherche </h1>
    <div class="row">
    <div class="col-md-4">
        <input class="ml-2" type="text" v-model="search_protocols" placeholder="Recherche.."/>
        <div v-for="protocol in filteredProtocols" class="row m-2">
            <div class="card clickable col-md-12" @click="showProtocol(protocol)">
                <div class="card-body">
                    {{protocol.title}}<br>
                    <small>
                        {{protocol.reference}}
                    </small>
                </div>
            </div>
        </div>
        <div class="ml-2" v-if="filteredProtocols.length === 0"> Aucun protocole accessible. </div>
    </div>

    <div v-if="selectedProtocol" class="col-md-8">
        <div class="row">
            <div class="col-md-12">
                <div v-if="editingTitle" class=""> 
                    <textarea rows="2" cols="70" v-model="selectedProtocol.title">{{selectedProtocol.title}}</textarea>
                    <input class="form_submit sticky-top" type="button" value="Ok" @click="editTitle">
                </div>
                <h5 v-else @click="editingTitle = true" class="mb-2 text-center">{{selectedProtocol.title}}</h5>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                Référence : {{selectedProtocol.reference}} <br>
            </div>
        </div>        
        
        <div class="row mt-3">
            <div class="col-md-12">
                <span class="font-weight-bold pb-2">Status</span>
                <div v-if="selectedProtocol.status=='draft'"> 
                    Non publié <br>
                    <button class="btn btn-sm btn-success mt-2" style="line-height: 1; font-size: 0.75rem;" @click="updateProtocolStatus('published')">Publier sur le dispositif information patient</button>
                </div>
                <div v-else-if="selectedProtocol.status=='published'">
                    Publié le {{ selectedProtocol.published_date | moment }} <br>
                    <button class="btn btn-sm btn-danger mt-2" style="line-height: 1; font-size: 0.75rem;" @click="updateProtocolStatus('closed')">Clôturer les oppositions</button>
                </div>
                <div v-else>
                    Publié le {{ selectedProtocol.published_date | moment }} et clôturé le {{selectedProtocol.closed_date | moment }} <br>
                    <button class="btn btn-sm btn-success mt-2" style="line-height: 1; font-size: 0.75rem;" @click="updateProtocolStatus('published')">Publier sur le dispositif information patient</button>
                </div>                
            </div>
        </div>

        
        <div class="row mt-3">
            <div class="col-md-12">
                <span class="font-weight-bold pb-2">Description</span>
                <div v-if="!selectedProtocol.short_desc && !editingDesc" @click="editingDesc = true">
                    Ajouter une description
                </div>
                <div v-if="editingDesc" class="mt-2"> 
                    <textarea v-model="selectedProtocol.short_desc" rows="4" cols="70" placeholder="Ajouter une courte description"></textarea>
                    <input class="form_submit sticky-top" type="button" value="Ok" @click="editDesc">
                </div>
                <p v-else @click="editingDesc = true" style="white-space: pre-line;">{{ selectedProtocol.short_desc }}</p>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-md-12">
                <span class="font-weight-bold">Gestion des collaborateurs</span> <br>
                <div class="mt-2">
                    <multiselect 
                    v-model="selectedProtocol.users" 
                    :options="users" 
                    @select=updateProtocol
                    @remove=updateProtocol
                    :custom-label="userFullName" 
                    placeholder="Ajouter des collaborateurs" 
                    label="id" track-by="id" 
                    :multiple="true"></multiselect>                
                </div>
            </div>
        </div>  
    
        <div class="row mt-4">
            <div class="col-md-12">
            <span class="font-weight-bold">Gestion des patients</span> <br>
            {{totalMaps}} patient(s) inclu(s) <br>

            Filtres :
            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="customSwitches" v-model="filterOpposition" @change="onFilterChange">
                <label class="custom-control-label" for="customSwitches">Opposition</label>
            </div>

            <!-- Ajouter search bar par IPP<br>
            Ajouter filtre pour voir toutes les oppositions<br> -->
                <vue-good-table
                    mode="remote"
                    @on-page-change="onPageChange"
                    @on-per-page-change="onPerPageChange"
                    :total-rows="totalMaps"
                    :columns="maps.columns"
                    :rows="maps.rows"
                    max-height="400px"
                    :pagination-options="{
                    enabled: true,
                    perPage: 10,
                    dropdownAllowAll: false,
                    nextLabel: 'Suivant',
                    prevLabel: 'Précédant',
                    mode: 'records',
                    rowsPerPageLabel: 'Afficher par',
                    ofLabel: 'sur',
                    }"
                    style-class="vgt-table condensed mt-2"
                    :sort-options="{enabled: false,}"
                    :search-options="{enabled: false}"
                    >
                    <template slot="table-row" slot-scope="props">
                        <span v-if="props.column.field == 'patient.birth_date'">
                            <span>{{ props.row.patient.birth_date | moment }}</span> 
                        </span>
                        <span v-else-if="props.column.field == 'register_date'">
                            <span>{{ props.row.patient.register_date | moment }}</span> 
                        </span>
                        <span v-else-if="props.column.field == 'register_by'">
                            <span>{{ props.row.register_by | user }}</span>
                        </span>
                        <span v-else-if="props.column.field == 'opposition'">
                            <span v-if="props.row.is_opposed">Opposition enregistrée le {{ props.row.opposition_date | moment}} <br> {{props.row | opposition_by}} </span>
                        </span>
                        <span v-else-if="props.column.field == 'actions'">
                            <span>
                            <i class="fa fa-lg fa-hand-paper" @click="updateMap(props.row, 'opposed')"></i>
                            <i class="fa fa-lg fa-trash" @click="deleteMap(props.row)"></i>
                            </span> 
                        </span>
                        <span v-else>
                        {{props.formattedRow[props.column.field]}}
                        </span>
                    </template>
                </vue-good-table>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-8">
                <span class="font-weight-bold">Gestion des cohortes</span> <br>
                <div class="row mt-2">
                    <div class="col">
                        <multiselect 
                        v-model="selectedCohort" 
                        :options="cohorts" 
                        track-by="id" 
                        label="title"
                        placeholder="Ajouter une cohorte" 
                        ></multiselect>
                    </div>
                
                    <div class="col">
                        <button class="btn btn-primary btn-sm" @click="associateCohort">Associer</button>                
                    </div>
                </div>
            </div>
        </div>  

        <div class="row">
            <div class="col-md-12">
                <vue-good-table
                    :columns="protocolCohorts.columns"
                    :rows="protocolCohorts.rows"
                    max-height="400px"
                    :pagination-options="{
                    enabled: true,
                    perPage: 10,
                    dropdownAllowAll: false,
                    nextLabel: 'Suivant',
                    prevLabel: 'Précédant',
                    mode: 'records',
                    rowsPerPageLabel: 'Afficher par',
                    ofLabel: 'sur',
                    }"
                    style-class="vgt-table condensed mt-2"
                    >
                    <template slot="table-row" slot-scope="props">
                        <span v-if="props.column.field == 'created_date'">
                            <span>{{ props.row.created_date | moment }}</span> 
                        </span>
                        <span v-else-if="props.column.field == 'last_sync_date'">
                            <span>{{ props.row.last_sync_date | moment }}</span> 
                        </span>
                        <span v-else-if="props.column.field == 'actions'">
                            <span>
                                <i class="fa fa-lg fa-sync" @click="syncCohort(props.row)"></i>
                            </span> 
                        </span>
                        <span v-else>
                        {{props.formattedRow[props.column.field]}}
                        </span>
                    </template>
                </vue-good-table>
            </div>
        </div>

<!--         <div>
            <input class="input small" type="text" v-model="search_cohorts" placeholder="Recherche.."/>
            <div class="table-responsive" style="max-height:200px;">
                <table class="mt-2 table table-sm table-striped">
                    <thead>
                        <th> # </th>
                        <th> Titre </th>
                        <th> Associé le </th>
                        <th> Inclus dans la cohorte </th>
                        <th> Inclus </th>
                        <th> Dernière synchronisation le </th>
                        <th> </th>
                    </thead>
                    <tbody style="font-size:13px;">
                        <tr v-for="cohort in filteredCohorts()">
                            <td> {{cohort.cohort}} </td>
                            <td> {{cohort.cohort_data.title}} </td> 
                            <td> {{cohort.created_date | moment}} </td> 
                            <td> {{cohort.cohort_data.nb_patients}} </td> 
                            <td> {{cohort.nb_patients_included}} </td> 
                            <td> {{cohort.last_sync_date | moment}} </td> 
                            <td> <img src="images/actualiser.png" style="cursor:pointer; width:20px;" @click="syncCohort(cohort)">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div> -->


        <div class="row mt-3">
            <div class="col-md-12">
                <b class="mt-2"> Actions </b><br>
                <small> 
                Copier coller une liste d'IPPs <br>
                Aller à la ligne entre chaque IPP (un copier coller depuis Excel fonctionnera)
                </small>
                <br>
                <textarea style="width:500px;" rows="10" v-model="patientsToAdd"></textarea>
                <br>
                <button v-if="!adding" class="btn btn-success btn-sm" @click="addPatients()"> Inclure </button>
                <button class="btn btn-danger btn-sm" @click="removePatients()"> Exclure </button>
                <button class="btn btn-primary btn-sm" @click="registerOppositions()"> Enregistrer l'opposition </button>

                <div v-if="addResults">
                    {{addResults.included_patients}} patient(s) inclu(s)
                    <div v-if="addResults.invalid_ipps.length != 0"> 
                        {{addResults.invalid_ipps.length}} ipp(s) invalide(s) : {{addResults.invalid_ipps_str}}
                    </div>
                    <div v-if="addResults.not_included_ipps.length != 0"> 
                        {{addResults.not_included_ipps.length}} patient(s) déjà inclus : {{addResults.not_included_ipps_str}}
                    </div>
                </div>
                <div v-if="removedResults">
                    {{removedResults.removed_patients}} patient(s) supprimés
                    <div v-if="removedResults.invalid_ipps.length != 0"> 
                        {{removedResults.invalid_ipps.length}} ipp(s) invalide(s) : {{removedResults.invalid_ipps_str}}
                    </div>
                </div>
                <div v-if="opposedResults">
                    {{opposedResults.opposed_patients}} patient(s) opposé(s)
                    <div v-if="opposedResults.invalid_ipps.length != 0"> 
                        {{opposedResults.invalid_ipps.length}} ipp(s) invalide(s) : {{opposedResults.invalid_ipps_str}}
                    </div>
                </div>
            </div>
        </div>
    </div>    
    
</div>
<script type="module" src="protocols.js"></script>

<? include "foot.php"; ?>