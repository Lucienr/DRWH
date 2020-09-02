import {Patient} from './APIService.js'
import {PatientToken} from './APIService.js'
import {Token} from './APIService.js'

new Vue({ 
  el: '#generate_token',
  data: {
    // Search patient
    searchIpp: '',
    patient: null,
    patientTokens: [],
    notFound: false,
    // Tokens list
    searchTokens: '',
    tokens: [],
    newToken: null,

  },
  methods: {
    async searchPatient() {
      this.patient=null;
      this.patientTokens=[];
      this.searchIpp = this.searchIpp.split(' ').join('').split('&').join('')
      if(this.searchIpp != '') {
          const data = await Patient.list({"ipp": this.searchIpp})
          if(data.length > 0) {
              this.notFound = false;
              this.patient = data[0];
              this.patientTokens = await PatientToken.list(this.patient.patient_num)
          } else {
              this.notFound = true;
          }
      } else {
          this.notFound = true;
      }
    },
    async generateToken() {
      var data = {
        "patient": this.patient.patient_num
      }
      const res = await Token.create(data)
      this.newToken=res;
      this.tokens.push(res);
      this.patientTokens.unshift(res);

    },
    async revokeToken(token) {
      const now = new Date();
      token.revoked_date = now;
      const res = await Token.update(token)
    }
  },
  mounted() {
    Token.list().then(data => this.tokens=data);
  },

  filters: {
    moment: function(date) {
        return moment(date).format('DD/MM/YYYY')
    },
  },
  computed: {
    filteredTokens() {
      return this.tokens.filter(token => {
          return token.patient_data.master_ipp.toLowerCase().includes(this.searchTokens.toLowerCase()) 
          || token.patient_data.firstname.toLowerCase().includes(this.searchTokens.toLowerCase()) 
          || token.patient_data.lastname.toLowerCase().includes(this.searchTokens.toLowerCase()) 
      })
    },
    
  },

});