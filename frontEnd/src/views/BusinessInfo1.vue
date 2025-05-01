<template>
  <v-container fluid class="login-page">
    <v-row class="fill-height">
      <v-col cols="12" md="6" class="form-section1">
        <v-container>
          <v-row justify="center">
            <v-col cols="12" md="8" class="text-center">
              <h1 class="title mb-3" style="font-size: xx-large !important">
                دعنا نتحدث قليلا عن العمل الخاص بك
              </h1>
            </v-col>
          </v-row>

          <v-row justify="center">
            <v-col cols="12" md="8">
              <v-form ref="form" v-model="valid">
                <!-- Loop for paired inputs -->
                <v-row v-for="(fieldPair, index) in pairedFields" :key="index">
                  <v-col
                    cols="12"
                    md="6"
                    v-for="(field, idx) in fieldPair"
                    :key="idx"
                  >
                    <p class="pargraph-name busnam">
                      {{ field.label }}
                      <v-icon v-if="field.required" color="red">*</v-icon>
                    </p>
                    <v-text-field
                      v-if="field.type !== 'select'"
                      :label="field.label"
                      :placeholder="field.placeholder"
                      :solo="true"
                      :rules="field.rules"
                      v-model="field.model"
                      :type="field.type || 'text'"
                      class="displ social"
                    ></v-text-field>
                    <v-select
                      v-else
                      :label="field.label"
                      :placeholder="field.placeholder"
                      :solo="true"
                      :rules="field.rules"
                      v-model="field.model"
                      :items="field.items"
                      :item-text="field.itemText"
                      :item-value="field.itemValue"
                      class="displ social"
                    ></v-select>
                  </v-col>
                </v-row>

                <!-- Description Field (Full Width) -->
                <v-col style="padding-left: 0; padding-right: 0">
                  <p class="pargraph-name busnam">
                    وصف العمل
                    <v-icon color="red">*</v-icon>
                  </p>
                  <v-text-field
                    label="وصف العمل"
                    placeholder="أكتب مفصل عن العمل"
                    solo
                    :rules="[rules.required]"
                    v-model="workDescription"
                    type="text"
                    class="displ social"
                  ></v-text-field>
                </v-col>

                <v-col>
                  <p class="pargraph-name busnam mb-3">
                    روابط وسائل التواصل الإجتماعي (إن وجد)
                  </p>

                  <v-row cols="12">
                    <v-col
                      v-for="(social, index) in socialLinks"
                      :key="index"
                      cols="12"
                    >
                      <v-row align="center">
                        <v-col cols="2" class="text-end" style="padding: 0">
                          <p class="pargraph-name busnam">{{ social.label }}</p>
                        </v-col>
                        <v-col cols="10" style="padding: 0">
                          <v-text-field
                            :label="social.label"
                            :placeholder="social.placeholder"
                            solo
                            v-model="social.model"
                            class="social"
                            type="url"
                          ></v-text-field>
                        </v-col>
                      </v-row>
                    </v-col>
                  </v-row>
                </v-col>

                <v-col style="padding-top: 30px">
                  <a class="forgot-password" @click="validateForm">
                    التالي
                    <v-btn icon color="#ffa726">
                      <v-icon>mdi-arrow-right</v-icon>
                    </v-btn>
                  </a>
                </v-col>
              </v-form>
            </v-col>
          </v-row>
        </v-container>
      </v-col>
      <v-col cols="12" md="6" class="image-section d-none d-md-flex">
        <v-img
          src="/photo/login/guy.jpg"
          class="image fill-height"
          contain
        ></v-img>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import api from '@/services/api';

export default {
  data() {
    return {
      valid: false,
      workDescription: "",
      rules: {
        required: (value) => !!value || "هذا الحقل مطلوب",
        email: (value) => {
          const pattern =
            /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
          return pattern.test(value) || "إيميل غير صحيح";
        },
      },
      fields: [
        {
          label: "اسم العمل",
          placeholder: "أدخل اسم العمل",
          model: "",
          required: true,
          rules: [(v) => !!v || "هذا الحقل مطلوب"],
        },
        {
          label: "البريد الإلكتروني",
          placeholder: "أدخل البريد الإلكتروني",
          model: "",
          required: true,
          rules: [
            (v) => !!v || "هذا الحقل مطلوب",
            (v) => /.+@.+\..+/.test(v) || "البريد الإلكتروني غير صحيح",
          ],
          type: "email",
        },
        {
          label: "الموقع الإلكتروني",
          placeholder: "أدخل الموقع الإلكتروني",
          model: "",
          required: false,
        },
        {
          label: "رقم الهاتف",
          placeholder: "أدخل رقم الهاتف",
          model: "",
          required: true,
          rules: [(v) => !!v || "هذا الحقل مطلوب"],
        },
        {
          label: "أوقات العمل",
          placeholder: "أدخل أوقات العمل",
          model: "",
          required: true,
          rules: [(v) => !!v || "هذا الحقل مطلوب"],
        },
        {
          label: "تصنيف العمل",
          placeholder: "اختر تصنيف العمل",
          model: null,
          required: true,
          rules: [(v) => !!v || "هذا الحقل مطلوب"],
          type: "select",
          items: [],
          itemText: "name_ar",
          itemValue: "id",
        },
      ],
      socialLinks: [
        {
          label: "Facebook",
          placeholder: "رابط Facebook",
          model: "",
        },
        {
          label: "Instagram",
          placeholder: "رابط Instagram",
          model: "",
        },
        {
          label: "Twitter",
          placeholder: "رابط Twitter",
          model: "",
        },
      ],
    };
  },
  async mounted() {
    // Fetch categories from API
    try {
      const categoriesResponse = await api.get('/categories');
      if (categoriesResponse.data.status === 'success' && categoriesResponse.data.categories) {
        this.fields[5].items = categoriesResponse.data.categories;
      }
    } catch (error) {
      console.error('Error fetching categories:', error);
    }

    // Fetch business details if available
    try {
      const response = await api.get('/business/details');
      if (response.data.status === 'success' && response.data.data) {
        const business = response.data.data;
        
        // Fill main fields
        this.fields[0].model = business.name;
        this.fields[1].model = business.email;
        this.fields[2].model = business.website;
        this.fields[3].model = business.phone;
        
        // Handle working times
        try {
          this.fields[4].model = typeof business.working_times === 'string' 
            ? JSON.parse(business.working_times) 
            : business.working_times;
        } catch (e) {
          console.error('Error parsing working times:', e);
          this.fields[4].model = business.working_times;
        }
        
        this.fields[5].model = business.category_id; // Set selected category
        this.workDescription = business.description;

        // Fill social links
        this.socialLinks[0].model = business.facebook_link;
        this.socialLinks[1].model = business.instagram_link;
        this.socialLinks[2].model = business.twitter_link;
      }
    } catch (error) {
      console.error('Error fetching business details:', error);
    }
  },
  computed: {
    pairedFields() {
      const pairs = [];
      for (let i = 0; i < this.fields.length; i += 2) {
        pairs.push(this.fields.slice(i, i + 2));
      }
      return pairs;
    },
  },
  methods: {
    validateForm() {
      if (this.$refs.form.validate()) {
        const businessData = {
          name: this.fields[0].model,
          email: this.fields[1].model,
          website: this.fields[2].model,
          phone: this.fields[3].model,
          working_times: this.fields[4].model,
          category_id: this.fields[5].model,
          description: this.workDescription,
          facebook_link: this.socialLinks[0].model,
          instagram_link: this.socialLinks[1].model,
          twitter_link: this.socialLinks[2].model
        };

        // Store the data in Vuex or local storage for BusinessInfo2
        this.$store.dispatch('setBusinessInfo1', businessData);
        this.$router.push('/Business2');
      } else {
        alert("الرجاء ملء جميع الحقول المطلوبة.");
      }
    },
  },
};
</script>

<style>
/*.login-page {
  padding-top: 70px !important;
}*/
.form-section1 {
  display: flex;
  flex-direction: column;
  justify-content: end;
  text-align: center;
  background-color: #fafafa;
  padding-top: 70px !important;
}
.busnam {
  font-size: large;
}

.social.v-text-field.v-text-field--solo .v-input__control {
  min-height: auto !important;
}

.social.v-text-field.v-text-field--enclosed .v-text-field__details {
  display: none !important;
}

.displ.v-text-field.v-text-field--enclosed .v-text-field__details {
  margin-bottom: 0 !important;
}

@media (max-width: 1272px) {
  /* .title {
    font-size: 1.5rem !important;
  } */

  .pargraph-name {
    font-size: medium !important;
  }

  .button {
    font-size: 0.9rem !important;
  }

  .busnam {
    font-size: medium;
  }

  .displ,
  .social {
    font-size: medium;
  }
}
</style>
