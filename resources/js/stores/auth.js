import axiosIns from "@/plugins/axios"
import { defineStore } from "pinia"

export const useAuthStore = defineStore("auth", {
  state: () => {
    var  access_token = localStorage.getItem("access_token")
    if (!access_token) {
      access_token = null
    }

    var role = localStorage.getItem("role")
    if (!role) {
      role = null
    }

    var user = JSON.parse(localStorage.getItem("user"))
    if (!user) {
      user = null
    }

    var profile = JSON.parse(localStorage.getItem("profile"))
    if (!profile) {
      profile = null
    }

    return {
      access_token,
      role,
      user,
      profile,
    }
  },
  getters: {
    isAuthenticated() {
      return this.access_token !== null && this.access_token !== undefined
    },
  },
  actions: {
    saveData(access_token) {
      localStorage.setItem("access_token", access_token)
      this.access_token = access_token
    },
    deleteData() {
      localStorage.removeItem("access_token")
      localStorage.removeItem("user")
      localStorage.removeItem("role")
      this.access_token = null
      this.user = null
      this.role = null
      this.profile = null
    },
    async getRoleUser() {
      // pindahin
    },
    async getUser() {
      if (!this.access_token) return
      try {
        const response = await axiosIns
          .get("/api/v1/account/profile")
  
        this.user = response.data.data.user
        localStorage.setItem("user", JSON.stringify(this.user))
        this.role = response.data.data.user.role
        localStorage.setItem("role", this.role)
        this.profile = response.data.data.profile
        localStorage.setItem("profile", JSON.stringify(this.profile))
      } catch (error) {
        console.log(error)
      }
    },
  },
})
