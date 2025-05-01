<template>
  <v-content>
    <v-container
      fluid
      class="background-container"
      :style="{ backgroundImage: 'url(' + background + ')' }"
      style="padding-top: 100px !important"
    >
      <v-row
        dir="rtl"
        class="container responsive-container"
        style="margin: auto; align-content: center; padding: 0px"
      >
        <!-- Text Section -->
        <v-col
          cols="12"
          sm="12"
          md="6"
          style="align-content: center"
          class="text-column"
        >
          <div class="bef" style="text-align: right">
            <p :style="{ color: headingColor }" class="aboutCom">
              {{ heading }}
            </p>
            <br />
            <h4 class="son son2">{{ description }}</h4>
            <br />
            <div style="justify-content: flex-start">
              <p class="pargraph">{{ additionalText }}</p>
            </div>
          </div>
        </v-col>

        <!-- Image Section -->
        <v-col class="left-column image-column" cols="12" sm="12" md="6">
          <div
            style="display: flex; justify-content: left"
            class="image-wrapper"
          >
            <img
              :src="imageSrc"
              :style="{ width: imageWidth + 'px', height: imageHeight }"
              alt="content image"
              class="responsive-image"
            />
          </div>
        </v-col>
      </v-row>
    </v-container>
  </v-content>
</template>

<script>
export default {
  name: "ContentSection",
  props: {
    background: {
      type: String,
      default: "/public/photo/v.png",
    },
    heading: {
      type: String,
      required: true,
    },
    headingColor: {
      type: String,
      default: "#4ac2cb",
    },
    description: {
      type: String,
      required: true,
    },
    additionalText: {
      type: String,
      default: "",
    },
    imageSrc: {
      type: String,
      required: true,
    },
    imageWidth: {
      type: String,
      default: "350px",
    },
    imageHeight: {
      type: String,
      default: "500px",
    },
    paddingTop: {
      type: Number,
      default: 100,
    },
  },
};
</script>

<style scoped>
.background-container {
  /* background-image: url("/public/photo/v.png"); */
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  min-height: 100px;
  /* min-width: 400px; */
  padding: 0;
  margin: 0;
  background-position: left;
  background-size: contain;
  /* padding-top: 100px !important; */
}

.text-column {
  text-align: right;
  padding: 20px;
}

.image-column {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 20px;
}

.responsive-container {
  margin: 0 auto;
  padding: 0 15px;
}

.aboutCom,
.aboutCom3 {
  /* color: #4ac2cb; */
  font-family: "ExpoLight";
  font-size: 20px;
  margin: auto !important;
}

.bef {
  /* background-image: url("/public/photo/background/cow3.png"); */
  background-size: 300px;
  /* background-position-x: 20px; */
  background-position-y: 120px;
  line-height: 1.3;
  /* text-align: right; */
}

.son {
  font-size: 32px;
  font-family: "ExpoBook", sans-serif;
  color: #34495e;
}

.son2 {
  max-width: 90%;
}

.pargraph {
  position: relative;
  padding-bottom: 1rem;
  font-size: 17px;
  font-family: "ExpoLight", sans-serif;
  bottom: 10px;
  /* max-width: 600px; */
  line-height: 1.5;
  max-width: 600px;
  justify-content: flex-start;
  color: #939393;
}

.read {
  padding: 20px 25px !important;
  font-size: 15px !important;
  border-radius: 7px !important;
  font-family: "ExpoLight" !important;
  font-weight: bold !important;
}

.left-column {
  padding-bottom: inherit !important;
}

.container {
  padding: 0px !important;
}

.responsive-image {
  /* max-width: 100%; */
  height: auto;
  border-radius: 8px;
}

.image-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
}

@media (max-width: 960px) {
  .image-column {
    display: none;
  }

  .text-column,
  .image-column {
    text-align: center;
  }

  .aboutCom {
    font-size: 18px;
  }

  .son {
    font-size: 1.2rem;
  }

  .pargraph {
    font-size: 0.9rem;
  }

  .read {
    font-size: 0.9rem;
  }
}

@media (max-width: 600px) {
  .aboutCom {
    font-size: 16px;
  }

  .son {
    font-size: 1rem;
  }

  .pargraph {
    font-size: 0.8rem;
  }

  .read {
    font-size: 0.8rem;
    padding: 10px 15px;
  }

  .responsive-image {
    max-width: 80%;
    height: auto;
  }
}
</style>
