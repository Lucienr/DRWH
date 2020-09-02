import {DWH_API_URL} from './parameters.js'

const API_URL = DWH_API_URL;

axios.interceptors.request.use((config) => {
  const accessHeader = { Authorization: 'Bearer ' + localStorage.getItem('access_token')}
  config.headers = accessHeader;
  return config;
}, (error) => {
   return Promise.reject(error);
})

axios.interceptors.response.use(function (response) {
    return response;
}, async function (error) {
    const response = error.response
    if (response.status === 401) {
      if (response.config.url === `${API_URL}/login`) {
        location = "connexion_user.php"
        return
      }
      var token_drwh = localStorage.getItem('access_token_drwh');
      const login_response = await axios.post(`${API_URL}/login`, {'token': token_drwh });
      localStorage.setItem('access_token', login_response.data.access_token);

      switch(response.config.method) {
        case 'get':
          return await axios.get(response.config.url);
        case 'post':
          return await axios.post(response.config.url, response.config.data);
        case 'put':
          return await axios.put(response.config.url, response.config.data);
      }
      return
    } else {
        return Promise.reject(error);
    }
});

class ReadOnlyAPI {
    constructor(resource) {
        this.resource = resource
        this.url_list = `${API_URL}/${this.resource}/`
    }

    url_detail(pk) {
        return `${this.url_list}${pk}/`
    }

    async list(params = {}) {
        const response = await axios.get(this.url_list, {params: params});
        return response.data;
    }

    async retrieve(pk) {
        const response = await axios.get(this.url_detail(pk));
        return response.data;
    }
}

class API extends ReadOnlyAPI {
    async create(object) {
        const response = await axios.post(this.url_list, object);
        return response.data;
    }

    async update(object) {
        const response = await axios.put(this.url_detail(object.id), object);
        return response.data;
    }

    async destroy(pk) {
        const response = await axios.delete(this.url_detail(pk));
        return response.data;
    }
}

class NestedAPI {
  constructor(base, resource) {
    this.base = base;
    this.resource = resource;
  }

  url_list(base_pk) {
    return `${API_URL}/${this.base}/${base_pk}/${this.resource}/`
  }

  url_detail(base_pk, resource_pk) {
    return `${this.url_list(base_pk)}${resource_pk}/`
  }

  async list(base_pk, params = {}) {
    const response = await axios.get(this.url_list(base_pk), {params: params});
    return response.data;
  }

  async retrieve(base_pk, resource_pk) {
      const response = await axios.get(this.url_detail(base_pk, resource_pk));
      return response.data;
  }

}

class ProtocolAPI extends API {
    // not in use
    async cohorts(pk) {
        let url = `${this.url_detail(pk)}cohorts/`
        const response = await axios.get(url);
        return response.data
    }
    
    // not in use
    async associate(protocol_pk, cohort) {
        let url = `${this.url_detail(protocol_pk)}cohorts/`
        const response = await axios.post(url, cohort);
        return response.data
    }

    // not in use
    async sync(protocol_pk, cohort) {
        let url = `${this.url_detail(protocol_pk)}cohorts/${cohort.id}/`
        const response = await axios.put(url, cohort);
        return response.data
    }

    async add_patients(protocol_pk, ipps) {
        let url = `${this.url_detail(protocol_pk)}add_patients/`
        const response = await axios.post(url, ipps);
        return response.data
    }

    async remove_patients(protocol_pk, ipps) {
      let url = `${this.url_detail(protocol_pk)}remove_patients/`
      const response = await axios.post(url, ipps);
      return response.data
    }

    async register_oppositions(protocol_pk, ipps) {
      let url = `${this.url_detail(protocol_pk)}register_oppositions/`
      const response = await axios.post(url, ipps);
      return response.data
    }

}


class CohortAPI extends API {
    async cohorts_result(pk) {
        let url = `${this.url_detail(pk)}cohorts-result/`
        const response = await axios.get(url);
        return response.data
    }

    async merge_cohorts(title, desc, cohort_one, cohort_two) {
        let data = {
          "title":title,
          "desc":desc,
          "cohort_one":cohort_one,
          "cohort_two":cohort_two
        }
        let url = `${this.url_list}merge_cohorts/`
        const response = await axios.post(url, data);
        return response.data
    }

    async create(data) {
      const response = await axios.post(this.url_list, data)
      return response.data
    }

    async delete(pk) {
      const response = await axios.delete(this.url_detail(pk))
      return response.data
    }

}


class SearchEngineAPI extends API {
  async search(data) {
    let url = `${this.url_list}search/`
    const response = await axios.post(url, data)
    return response.data
  }
}


export const Protocol = new ProtocolAPI('protocols')
export const MapProtocolPatient = new API('maps')
export const Patient = new API('patients')
export const User = new API('users')
export const PatientOpposition = new API('patient_oppositions')
export const Cohort = new CohortAPI('cohorts')
export const SearchEngine = new SearchEngineAPI('search-engine')

// Generate token
export const Token = new API('tokens')
export const PatientToken = new NestedAPI('patients', 'tokens')