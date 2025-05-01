<template>
  <v-container fluid class="login-page">
    <v-row class="fill-height">
      <v-col cols="12" md="6" class="form-section1">
        <v-container>
          <v-row justify="center">
            <v-col cols="12" md="8">
              <v-form ref="form" v-model="valid" style="padding-top: inherit">
                <p class="pargraph-name busnam titlebus">مميزات إضافية</p>

                <!-- Loop through checkboxes -->
                <v-row class="mb-5">
                  <v-col
                    cols="6"
                    v-for="(checkbox, index) in checkboxes"
                    :key="index"
                    class="column"
                  >
                    <v-checkbox
                      v-model="checkbox.selected"
                      :color="checkbox.color || 'success'"
                      :label="checkbox.label"
                      hide-details
                    ></v-checkbox>
                  </v-col>
                </v-row>

                <v-col class="column">
                  <p class="titlebus pargraph-name">صورة الغلاف</p>
                  <div class="file-upload">
                    <label class="upload-label" for="file-input">
                      <div v-if="preview" class="preview-container">
                        <img
                          :src="preview"
                          alt="Uploaded Image"
                          class="preview-image"
                        />
                      </div>
                      <div v-else class="icon-container">
                        <v-icon color="grey" size="32">mdi-arrow-down</v-icon>
                      </div>
                    </label>
                    <input
                      id="file-input"
                      type="file"
                      accept="image/*"
                      @change="handleFileUpload"
                      class="file-input"
                    />
                  </div>
                </v-col>

                <v-col class="column" style="padding-top: 20px">
                  <p style="justify-self: right" class="titlebus">
                    صور تعرض العمل (إن وجد)
                  </p>
                  <v-row style="justify-content: space-around">
                    <div
                      v-for="(image, index) in images"
                      :key="index"
                      class="file-upload"
                    >
                      <label class="upload-label" :for="`file-input-${index}`">
                        <div v-if="image.preview2" class="preview-container">
                          <img
                            :src="image.preview2"
                            alt="Uploaded Image"
                            class="preview-image"
                          />
                          <div class="remove-image" @click.stop="removeImage(index)">
                            <v-icon color="white">mdi-close</v-icon>
                          </div>
                        </div>
                        <div v-else class="icon-container">
                          <v-icon color="grey" size="32">mdi-arrow-down</v-icon>
                        </div>
                      </label>
                      <input
                        :id="`file-input-${index}`"
                        type="file"
                        accept="image/*"
                        @change="handleFileUpload2($event, index)"
                        class="file-input"
                      />
                    </div>
                  </v-row>
                </v-col>
                <v-divider class="my-16"></v-divider>
                <v-col class="">
                  <v-row>
                    <p
                      class="pargraph-name busnam titlebus"
                      style="padding-bottom: 15px"
                    >
                      أدخل الإحداثيات لموقع العمل
                      <a
                        href="https://www.latlong.net/"
                        class="link"
                        style="font-size: medium"
                        target="_blank"
                        type="text"
                      >
                        (يمكنك الحصول علبهم من هذا الموقع latlong.net)
                      </a>
                    </p>
                    <v-row>
                      <v-col>
                        <p class="pargraph-name busnam">
                          Latitude
                          <v-icon color="red">*</v-icon>
                        </p>
                        <v-text-field
                          v-model="latitude"
                          label="latitude"
                          placeholder="latitude"
                          solo
                          :rules="[rules.required]"
                          type="number"
                        ></v-text-field>
                      </v-col>
                      <v-col>
                        <p class="pargraph-name busnam">
                          Longitude
                          <v-icon color="red">*</v-icon>
                        </p>
                        <v-text-field
                          v-model="longitude"
                          label="longitude"
                          placeholder="longitude"
                          solo
                          :rules="[rules.required]"
                          type="number"
                        ></v-text-field>
                      </v-col>
                    </v-row>
                  </v-row>
                </v-col>

                <v-col style="padding: 0">
                  <v-btn
                    color="orange"
                    block
                    large
                    outlined
                    class="button"
                    @click="validateForm"
                  >
                    حفظ بيانات العمل
                  </v-btn>
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
import { API_URL } from '@/services/api';

export default {
  data() {
    return {
      valid: false,
      preview: null,
      street: "",
      latitude: "",
      longitude: "",
      files: [],
      images: [
        { preview2: null },
        { preview2: null },
        { preview2: null },
        { preview2: null },
      ],
      rules: {
        required: (value) => !!value || "هذا الحقل مطلوب",
      },
      checkboxes: [
        { label: "تتوفر خدمة WI-FI", selected: false },
        { label: "تتوفر خدمة الحجز عبر الإنترنت", selected: false },
        { label: "الموقع قريب على المواصلات", selected: false },
        { label: "تتوفر مواقف للسيارات", selected: false },
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
      fields: [
        {
          label: "العنوان",
          placeholder: "أدخل عنوان العمل",
          model: "",
          required: true,
          rules: [(v) => !!v || "هذا الحقل مطلوب"],
        },
        {
          label: "المدينة",
          placeholder: "أدخل المدينة",
          model: "",
          required: true,
          rules: [(v) => !!v || "هذا الحقل مطلوب"],
        },
        {
          label: "المنطقة",
          placeholder: "أدخل المنطقة",
          model: "",
          required: true,
          rules: [(v) => !!v || "هذا الحقل مطلوب"],
        },
        {
          label: "الرمز البريدي",
          placeholder: "أدخل الرمز البريدي",
          model: "",
          required: true,
          rules: [(v) => !!v || "هذا الحقل مطلوب"],
        },
      ],
      businessInfo1: null,
      isUpdating: false
    };
  },
  async mounted() {
    // Get business info from store
    this.businessInfo1 = this.$store.state.businessInfo1;

    try {
      // Fetch business details if available
      const response = await api.get('/business/details');
      if (response.data.status === 'success' && response.data.data) {
        this.isUpdating = true;
        const business = response.data.data;

        // Set checkbox values based on business data
        this.checkboxes[0].selected = business.has_wifi;
        this.checkboxes[1].selected = business.has_online_booking;
        this.checkboxes[2].selected = business.near_transportation;
        this.checkboxes[3].selected = business.has_parking;

        // Set location values
        this.latitude = business.latitude;
        this.longitude = business.longitude;

        // Prepare business info for form data
        if (!this.businessInfo1) {
          this.businessInfo1 = {
            name: business.name,
            email: business.email,
            website: business.website,
            phone: business.phone,
            working_times: business.working_times,
            type: business.type,
            category_id: business.category_id,
            description: business.description,
            facebook_link: business.facebook_link,
            instagram_link: business.instagram_link,
            twitter_link: business.twitter_link
          };
        }
        
        // Fill address fields
        this.fields[0].model = business.address;
        this.fields[1].model = business.city;
        this.fields[2].model = business.area;
        this.fields[3].model = business.postal_code;

        // Fill main picture if exists
        if (business.main_picture_url) {
          this.preview = business.main_picture_url;
        }

        // Fill gallery pictures if they exist
        if (business.galleryPicturesUrls || business.gallery_picture_urls) {
          console.log('Gallery Pictures Type:', typeof business.gallery_pictures);
          console.log('Gallery Pictures:', business.gallery_picture_urls);
          
          // Clear existing images
          this.images = [];
          
          // Handle different formats of gallery_pictures
          let galleryPictures = [];
          
          // Try to get gallery images from the new format first (via accessor)
          if (business.galleryPicturesUrls) {
            galleryPictures = business.galleryPicturesUrls;
          } else if (Array.isArray(business.gallery_picture_urls)) {
            galleryPictures = business.gallery_picture_urls;
          } else if (typeof business.gallery_pictures === 'string') {
            try {
              galleryPictures = JSON.parse(business.gallery_pictures);
            } catch (e) {
              console.error('Error parsing gallery_pictures:', e);
              galleryPictures = [business.gallery_pictures];
            }
          } else if (business.gallery_pictures) {
            galleryPictures = [business.gallery_pictures];
          }
          
          // Create new image objects for each gallery picture
          galleryPictures.forEach((picture, index) => {
            if (picture) {
              const imageUrl = picture.startsWith(API_URL) || picture.startsWith('http')
                ? picture
                : `${API_URL}/business/image/${picture}`;
              
              console.log(`Image ${index} URL:`, imageUrl);
              
              // Add new image object to the array
              this.images.push({ preview2: imageUrl });
            }
          });
          
          // Fill remaining slots with empty objects if needed
          while (this.images.length < 4) {
            this.images.push({ preview2: null });
          }
          
          console.log('Final Images Array:', this.images);
        }
      }
    } catch (error) {
      console.error('Error fetching business details:', error);
    }
  },
  methods: {
    handleFileUpload(event) {
      const file = event.target.files[0];
      if (file) {
        this.preview = URL.createObjectURL(file);
        this.files.push(file);
      }
    },
    handleFileUpload2(event, index) {
      const file = event.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
          // If this slot already has an image, create a new slot
          if (this.images[index].preview2 && !this.images[index].preview2.startsWith('data:image')) {
            // This is an existing image, so we add a new image instead of replacing it
            this.images.push({ preview2: e.target.result });
            // Store the file in the files array at the correct position
            this.files[this.images.length] = file;
          } else {
            // This is either an empty slot or a new image (not yet saved), so we can replace it
            this.images[index].preview2 = e.target.result;
            // Store the file in the files array at the correct position
            this.files[index + 1] = file;
          }
        };
        reader.readAsDataURL(file);
      }
    },
    async validateForm() {
      if (this.$refs.form.validate()) {
        try {
          const formData = new FormData();
          
          // Add business info from BusinessInfo1
          const businessInfo1 = this.$store.state.businessInfo1;
          if (!businessInfo1) {
            alert('لم يتم العثور على معلومات الشركة الأساسية. يرجى العودة وإعادة المحاولة.');
            return;
          }

          // Format and validate URLs
          const formatUrl = (url) => {
            if (!url) return '';
            return url.startsWith('http://') || url.startsWith('https://') ? url : `https://${url}`;
          };

          // Add business info with proper formatting
          formData.append('name', businessInfo1.name);
          formData.append('email', businessInfo1.email);
          formData.append('website', formatUrl(businessInfo1.website));
          formData.append('phone', businessInfo1.phone);
          formData.append('working_times', JSON.stringify(businessInfo1.working_times));
          formData.append('category_id', businessInfo1.category_id);
          formData.append('description', businessInfo1.description);
          formData.append('facebook_link', formatUrl(businessInfo1.facebook_link));
          formData.append('instagram_link', formatUrl(businessInfo1.instagram_link));
          formData.append('twitter_link', formatUrl(businessInfo1.twitter_link));

          // Add amenities as boolean values
          formData.append('has_wifi', this.checkboxes[0].selected ? '1' : '0');
          formData.append('has_online_booking', this.checkboxes[1].selected ? '1' : '0');
          formData.append('near_transportation', this.checkboxes[2].selected ? '1' : '0');
          formData.append('has_parking', this.checkboxes[3].selected ? '1' : '0');

          // Add location
          if (!this.latitude || !this.longitude) {
            alert('الرجاء إدخال إحداثيات الموقع');
            return;
          }
          formData.append('latitude', this.latitude);
          formData.append('longitude', this.longitude);

          // Handle main picture
          if (this.files[0]) {
            // If new file was uploaded
            formData.append('main_picture', this.files[0]);
          } else if (!this.isUpdating) {
            // If creating new business and no picture was uploaded
            alert('الرجاء تحميل صورة الغلاف');
            return;
          }
          // If updating and no new file was uploaded, we don't need to send main_picture
          // as the existing one will be kept on the server

          // Handle gallery pictures
          const existingGalleryPictures = [];
          const newGalleryPictures = [];
          
          // Process each image entry
          this.images.forEach((img, index) => {
            if (img.preview2) {
              // Check if this is a URL (existing image) or a base64 string (new file)
              if (img.preview2.startsWith('data:image')) {
                // This is a new file, it will be handled below with the files array
                const fileIndex = index + 1; // +1 because index 0 is main picture
                if (this.files[fileIndex]) {
                  newGalleryPictures.push({
                    index: index,
                    file: this.files[fileIndex]
                  });
                }
              } else {
                // This is an existing image URL
                existingGalleryPictures.push(img.preview2);
              }
            }
          });
          
          // Add existing gallery pictures as JSON string
          if (existingGalleryPictures.length > 0) {
            formData.append('existing_gallery_pictures', JSON.stringify(existingGalleryPictures));
            console.log('Existing gallery pictures:', existingGalleryPictures);
          }
          
          // Add new gallery pictures as individual files
          newGalleryPictures.forEach((item, index) => {
            formData.append(`gallery_pictures[${index}]`, item.file);
          });
          console.log('New gallery pictures count:', newGalleryPictures.length);

          // Show loading state
          this.loading = true;

          // Submit to backend
          const response = await api.post('/business/details', formData, {
            headers: {
              'Content-Type': 'multipart/form-data',
              'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
          });

          if (response.data.status === 'success') {
            this.$store.dispatch('setSuccessMessage', 'تم حفظ معلومات الشركة بنجاح');
            this.$router.push('/');
            alert('تم حفظ معلومات الشركة بنجاح');
          } else {
            alert(response.data.message || 'حدث خطأ أثناء حفظ المعلومات');
          }
        } catch (error) {
          console.error('Error saving business details:', error);
          
          if (error.response) {
            if (error.response.data.errors) {
              alert(Object.values(error.response.data.errors).flat().join('\n'));
            } else if (error.response.data.message) {
              alert(error.response.data.message);
            } else {
              alert('حدث خطأ أثناء حفظ المعلومات');
            }
          } else if (error.request) {
            alert('تعذر الاتصال بالخادم. يرجى التحقق من اتصالك بالإنترنت');
          } else {
            alert(error.message || 'حدث خطأ أثناء حفظ المعلومات');
          }
        } finally {
          this.loading = false;
        }
      } else {
        alert('الرجاء ملء جميع الحقول المطلوبة');
      }
    },
    async submitForm() {
      if (this.$refs.form.validate()) {
        try {
          const businessData = {
            ...this.businessInfo1,
            address: this.fields[0].model,
            city: this.fields[1].model,
            area: this.fields[2].model,
            postal_code: this.fields[3].model
          };

          if (this.isUpdating) {
            await api.put('/business/update', businessData);
          } else {
            await api.post('/business/create', businessData);
          }

          this.$router.push('/dashboard');
        } catch (error) {
          console.error('Error saving business details:', error);
          alert('حدث خطأ أثناء حفظ البيانات. يرجى المحاولة مرة أخرى.');
        }
      } else {
        alert("الرجاء ملء جميع الحقول المطلوبة.");
      }
    },
    goBack() {
      this.$router.push('/Business1');
    },
    removeImage(index) {
      const image = this.images[index];
      if (image.preview2) {
        if (image.preview2.startsWith('data:image')) {
          // This is a new unsaved image, just remove it from the array
          this.images[index].preview2 = null;
          // Remove the file from the files array if it exists
          if (this.files[index + 1]) {
            delete this.files[index + 1];
          }
        } else {
          // This is an existing image, call the API to delete it
          // Extract the filename from the full URL
          const imagePath = image.preview2.split('/').pop();
          
          // Call the API to delete the image
          api.post('/business/delete-gallery-image', {
            image_path: imagePath
          })
          .then(response => {
            if (response.data.status === 'success') {
              // Remove the image from the local array
              this.images[index].preview2 = null;
              this.$store.dispatch('showSuccess', 'تم حذف الصورة بنجاح');
            } else {
              this.$store.dispatch('showError', response.data.message || 'فشل في حذف الصورة');
            }
          })
          .catch(error => {
            console.error('Error deleting image:', error);
            this.$store.dispatch('showError', 'فشل في حذف الصورة. يرجى المحاولة مرة أخرى');
          });
        }
      }
    },
    async deleteImage(imagePath) {
      try {
        const response = await api.post('/business/delete-gallery-image', {
          image_path: imagePath
        });
        
        if (response.data.success) {
          // Remove the image from the local images array
          this.images = this.images.filter(img => img.preview2 !== imagePath);
          this.$store.dispatch('showSuccess', 'Image deleted successfully');
        } else {
          this.$store.dispatch('showError', response.data.message || 'Failed to delete image');
        }
      } catch (error) {
        console.error('Error deleting image:', error);
        this.$store.dispatch('showError', 'Failed to delete image. Please try again.');
      }
    }
  },
};
</script>

<style scoped>
.column {
  padding: 0;
}

.file-upload {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 20%;
  height: 85px;
  border: 1px solid #ccc;
  border-radius: 8px;
  background-color: #f9f9f9;
  position: relative;
  overflow: hidden;
  cursor: pointer;
  margin: 5px;
}

.file-input {
  display: none;
}

.upload-label {
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  position: relative;
}

.icon-container {
  display: flex;
  justify-content: center;
  align-items: center;
}

.preview-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
  width: 100%;
  position: relative;
}

.preview-image {
  max-width: 100%;
  max-height: 100%;
  object-fit: cover;
  border-radius: 8px;
}

.remove-image {
  position: absolute;
  top: 5px;
  right: 5px;
  background-color: rgba(0, 0, 0, 0.5);
  border-radius: 50%;
  width: 24px;
  height: 24px;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  z-index: 1;
}

.remove-image:hover {
  background-color: rgba(0, 0, 0, 0.7);
}

.titlebus {
  justify-self: right;
  font-size: larger;
  font-weight: bold;
  color: #34495e !important;
}
</style>
