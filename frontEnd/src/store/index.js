import Vue from "vue";
import Vuex from "vuex";

Vue.use(Vuex);

export default new Vuex.Store({
  state: {
    businessInfo1: null
  },
  getters: {
    getBusinessInfo1: state => state.businessInfo1
  },
  mutations: {
    SET_BUSINESS_INFO1(state, data) {
      state.businessInfo1 = data;
    }
  },
  actions: {
    setBusinessInfo1({ commit }, data) {
      commit('SET_BUSINESS_INFO1', data);
    }
  },
  modules: {},
});
