import { computed, ref } from "vue";
import axios from "axios";

export function useBookingWizard(props, preselectedBarber = null) {
    const step = ref("services");

    const selectedMainService = ref(null);
    const selectedServiceOption = ref(null);
    const selectedAddon = ref(null);

    const staffMode = ref(preselectedBarber ? "barber" : null);
    const selectedBarber = ref(preselectedBarber);

    const selectedDate = ref("");
    const selectedSlot = ref(null);
    const slots = ref([]);

    const loadingSlots = ref(false);
    const creatingReservation = ref(false);
    const uploadingReceipt = ref(false);

    const errorMessage = ref("");
    const successMessage = ref("");
    const createdReservation = ref(null);
    const reservationCreated = ref(false);

    const receiptFile = ref(null);
    const receiptPreview = ref("");
    const receiptUploaded = ref(false);
    const receiptUrl = ref("");

    const paymentOption = ref("at_appointment");

    const customer = ref({
        name: "",
        lastName: "",
        phone: "",
        email: "",
    });

    const mainServices = computed(() => {
        return props.services
            .filter((service) => service.service_type === "main")
            .sort((a, b) => Number(a.sort_order) - Number(b.sort_order));
    });

    const serviceOptions = computed(() => {
        if (!selectedMainService.value) {
            return [];
        }

        return props.services
            .filter((service) => {
                return (
                    service.service_type === "option" &&
                    Number(service.parent_service_id) ===
                        Number(selectedMainService.value.id)
                );
            })
            .sort((a, b) => Number(a.sort_order) - Number(b.sort_order));
    });

    const addons = computed(() => {
        return props.services
            .filter((service) => service.service_type === "addon")
            .sort((a, b) => Number(a.sort_order) - Number(b.sort_order));
    });

    const bookableService = computed(() => {
        if (selectedServiceOption.value) return selectedServiceOption.value;
        if (selectedMainService.value && serviceOptions.value.length === 0) return selectedMainService.value;
        return null;
    });

    const total = computed(() => {
        let amount = 0;

        if (bookableService.value) {
            amount += Number(bookableService.value.price);
        }

        if (selectedAddon.value) {
            amount += Number(selectedAddon.value.price);
        }

        return amount;
    });

    const totalDuration = computed(() => {
        let minutes = 0;

        if (bookableService.value) {
            minutes += Number(bookableService.value.duration_minutes);
        }

        if (selectedAddon.value) {
            minutes += Number(selectedAddon.value.duration_minutes);
        }

        return minutes;
    });

    const selectedBarberName = computed(() => {
        if (staffMode.value === "any") {
            return "Cualquier miembro del personal";
        }

        return selectedBarber.value?.display_name || "Sin seleccionar";
    });

    const finalBarberProfile = computed(() => {
        if (staffMode.value === "any") {
            return props.barbers[0] || null;
        }

        return selectedBarber.value;
    });

    const today = computed(() => {
        return new Date().toISOString().split("T")[0];
    });

    const canContinueFromServices = computed(() => {
        return Boolean(selectedMainService.value);
    });

    const canContinueFromStaff = computed(() => {
        return staffMode.value === "any" || Boolean(selectedBarber.value);
    });

    const canContinueFromSchedule = computed(() => {
        return Boolean(selectedDate.value && selectedSlot.value);
    });

    const payToday = computed(() => paymentOption.value === "today");
    const payAtAppointment = computed(() => paymentOption.value === "at_appointment");

    const canCreateReservation = computed(() => {
        return Boolean(
            bookableService.value &&
            finalBarberProfile.value &&
            selectedDate.value &&
            selectedSlot.value &&
            String(customer.value.name || "").trim() &&
            String(customer.value.phone || "").trim(),
        );
    });

    const clearSchedule = () => {
        selectedDate.value = "";
        selectedSlot.value = null;
        slots.value = [];
    };

    const clearStaff = () => {
        staffMode.value = null;
        selectedBarber.value = null;
    };

    const clearReceipt = () => {
        receiptFile.value = null;
        receiptPreview.value = "";
        receiptUploaded.value = false;
        receiptUrl.value = "";
    };

    const selectMainService = (service) => {
        selectedMainService.value = service;
        selectedServiceOption.value = null;
        selectedAddon.value = null;

        clearStaff();
        clearSchedule();
        clearReceipt();

        errorMessage.value = "";
        successMessage.value = "";
    };

    const selectServiceOption = (service) => {
        selectedServiceOption.value = service;

        clearStaff();
        clearSchedule();
        clearReceipt();

        errorMessage.value = "";
        successMessage.value = "";
    };

    const toggleAddon = (addon) => {
        if (selectedAddon.value?.id === addon.id) {
            selectedAddon.value = null;
        } else {
            selectedAddon.value = addon;
        }

        clearSchedule();
        clearReceipt();

        errorMessage.value = "";
        successMessage.value = "";
    };

    const selectAnyStaff = () => {
        staffMode.value = "any";
        selectedBarber.value = null;

        clearSchedule();
        clearReceipt();

        errorMessage.value = "";
        successMessage.value = "";
    };

    const selectBarber = (barber) => {
        staffMode.value = "barber";
        selectedBarber.value = barber;

        clearSchedule();
        clearReceipt();

        errorMessage.value = "";
        successMessage.value = "";
    };

    const goToServices = () => {
        step.value = "services";
        errorMessage.value = "";
        successMessage.value = "";
    };

    const goToStaff = () => {
        if (!canContinueFromServices.value) {
            errorMessage.value = "Selecciona un servicio.";
            return;
        }

        errorMessage.value = "";
        step.value = "staff";
    };

    const goToSchedule = () => {
        if (!canContinueFromStaff.value) {
            errorMessage.value = "Selecciona el personal.";
            return;
        }

        errorMessage.value = "";
        step.value = "schedule";
    };

    const goToCheckout = () => {
        if (!canContinueFromSchedule.value) {
            errorMessage.value = "Selecciona fecha y hora.";
            return;
        }

        errorMessage.value = "";
        step.value = "checkout";
    };

    const loadAvailability = async () => {
        errorMessage.value = "";
        successMessage.value = "";
        selectedSlot.value = null;
        slots.value = [];

        if (
            !bookableService.value ||
            !finalBarberProfile.value ||
            !selectedDate.value
        ) {
            return;
        }

        loadingSlots.value = true;

        try {
            const response = await axios.post("/api/barberia/availability/check", {
                service_id: bookableService.value.id,
                addon_service_id: selectedAddon.value?.id || null,
                barber_profile_id: finalBarberProfile.value.id,
                date: selectedDate.value,
            });

            slots.value = response.data.slots || [];
        } catch (error) {
            errorMessage.value =
                error.response?.data?.message ||
                "No se pudo consultar la disponibilidad.";
            slots.value = [];
        } finally {
            loadingSlots.value = false;
        }
    };

    const slotsMorning = computed(() => {
        return slots.value.filter((slot) => {
            const hour = Number(String(slot.start_time).split(":")[0]);
            return hour < 12;
        });
    });

    const slotsAfternoon = computed(() => {
        return slots.value.filter((slot) => {
            const hour = Number(String(slot.start_time).split(":")[0]);
            return hour >= 12 && hour < 18;
        });
    });

    const slotsNight = computed(() => {
        return slots.value.filter((slot) => {
            const hour = Number(String(slot.start_time).split(":")[0]);
            return hour >= 18;
        });
    });

    const createReservation = async () => {
        errorMessage.value = "";
        successMessage.value = "";

        const missingFields = [];

        if (!bookableService.value) {
            missingFields.push("servicio");
        }

        if (!finalBarberProfile.value) {
            missingFields.push("barbero");
        }

        if (!selectedDate.value) {
            missingFields.push("fecha");
        }

        if (!selectedSlot.value) {
            missingFields.push("hora");
        }

        if (!String(customer.value.name || "").trim()) {
            missingFields.push("nombre");
        }

        if (!String(customer.value.phone || "").trim()) {
            missingFields.push("teléfono");
        }

        if (missingFields.length > 0) {
            errorMessage.value = `Faltan estos campos: ${missingFields.join(", ")}.`;
            return;
        }

        creatingReservation.value = true;

        try {
            const response = await axios.post("/api/barberia/reservations", {
                barber_shop_id: props.shop.id,
                service_id: bookableService.value.id,
                addon_service_id: selectedAddon.value?.id || null,
                barber_profile_id: finalBarberProfile.value.id,
                customer_name:
                    `${customer.value.name} ${customer.value.lastName}`.trim(),
                customer_phone: String(customer.value.phone).trim(),
                customer_email: customer.value.email
                    ? String(customer.value.email).trim()
                    : null,
                reservation_date: selectedDate.value,
                start_time: selectedSlot.value.start_time,
                payment_option: paymentOption.value,
            });

            createdReservation.value = response.data.reservation;
            successMessage.value = "¡Su cita ha sido reservada con éxito!";
            reservationCreated.value = true;

            if (paymentOption.value === "at_appointment") {
                setTimeout(() => {
                    window.location.href = "/";
                }, 2000);
            }
        } catch (error) {
            errorMessage.value =
                error.response?.data?.message || "No se pudo crear la reserva.";
        } finally {
            creatingReservation.value = false;
        }
    };

    const setReceiptFile = (event) => {
        const file = event.target.files?.[0];

        receiptFile.value = null;
        receiptPreview.value = "";
        receiptUploaded.value = false;
        receiptUrl.value = "";

        if (!file) {
            return;
        }

        receiptFile.value = file;
        receiptPreview.value = URL.createObjectURL(file);
    };

    const uploadReceipt = async () => {
        errorMessage.value = "";
        successMessage.value = "";

        if (!createdReservation.value?.id) {
            errorMessage.value = "Primero debes crear la reserva.";
            return;
        }

        if (!receiptFile.value) {
            errorMessage.value = "Selecciona una imagen del comprobante.";
            return;
        }

        uploadingReceipt.value = true;

        try {
            const formData = new FormData();
            formData.append("receipt_image", receiptFile.value);
            formData.append("customer_name", createdReservation.value.customer_name);
            formData.append("customer_phone", createdReservation.value.customer_phone);

            const response = await axios.post(
                `/api/barberia/reservations/${createdReservation.value.id}/receipt`,
                formData,
                {
                    headers: {
                        "Content-Type": "multipart/form-data",
                    },
                },
            );

            createdReservation.value = response.data.reservation;
            receiptUploaded.value = true;
            receiptUrl.value = response.data.receipt_url || "";
            successMessage.value =
                response.data.message || "Comprobante subido correctamente.";

            setTimeout(() => {
                window.location.href = "/";
            }, 2000);
        } catch (error) {
            errorMessage.value =
                error.response?.data?.message ||
                "No se pudo subir el comprobante.";
        } finally {
            uploadingReceipt.value = false;
        }
    };

    const resetFlow = () => {
        step.value = "services";

        selectedMainService.value = null;
        selectedServiceOption.value = null;
        selectedAddon.value = null;

        clearStaff();
        clearSchedule();
        clearReceipt();

        loadingSlots.value = false;
        creatingReservation.value = false;
        uploadingReceipt.value = false;

        errorMessage.value = "";
        successMessage.value = "";
        createdReservation.value = null;
        reservationCreated.value = false;
        paymentOption.value = "at_appointment";

        customer.value = {
            name: "",
            lastName: "",
            phone: "",
            email: "",
        };
    };

    const formatDate = (date) => {
        if (!date) {
            return "Sin seleccionar";
        }

        const cleanDate = String(date).substring(0, 10);

        return new Date(`${cleanDate}T00:00:00`).toLocaleDateString("es-EC", {
            weekday: "long",
            year: "numeric",
            month: "long",
            day: "numeric",
        });
    };

    const formatTime = (time) => {
        if (!time) {
            return "";
        }

        const [hour, minute] = String(time).split(":");
        const date = new Date();

        date.setHours(Number(hour), Number(minute), 0, 0);

        return date.toLocaleTimeString("es-EC", {
            hour: "numeric",
            minute: "2-digit",
        });
    };

    return {
        step,

        selectedMainService,
        selectedServiceOption,
        selectedAddon,

        staffMode,
        selectedBarber,

        selectedDate,
        selectedSlot,
        slots,

        loadingSlots,
        creatingReservation,
        uploadingReceipt,

        errorMessage,
        successMessage,
        createdReservation,
        reservationCreated,

        receiptFile,
        receiptPreview,
        receiptUploaded,
        receiptUrl,

        customer,
        paymentOption,
        payToday,
        payAtAppointment,

        mainServices,
        serviceOptions,
        addons,
        bookableService,
        total,
        totalDuration,
        selectedBarberName,
        finalBarberProfile,
        today,

        canContinueFromServices,
        canContinueFromStaff,
        canContinueFromSchedule,
        canCreateReservation,

        selectMainService,
        selectServiceOption,
        toggleAddon,
        selectAnyStaff,
        selectBarber,

        goToServices,
        goToStaff,
        goToSchedule,
        goToCheckout,

        loadAvailability,

        slotsMorning,
        slotsAfternoon,
        slotsNight,

        createReservation,
        setReceiptFile,
        uploadReceipt,
        resetFlow,

        formatDate,
        formatTime,
    };
}
