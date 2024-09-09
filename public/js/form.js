$(document).ready(function () {
    $('#example').DataTable();

    // handleStepChange(1);

    /* For date month year */
    $('.datepickerdate').datepicker({
        format: "mm/dd/yyyy",
        startView: "days",
        todayHighlight: true,
        startDate: new Date(),
        minViewMode: "days"
    })


    window.hidePhysicalSection = function (pysicalElem) {
        if ($(pysicalElem).val() != "" && $(pysicalElem).val() == 0) {
            $('.physicalActivitySection').addClass('d-none')
            $('.physicalActivitySection').find('input[type="number"]').val('');
            $('.physicalActivitySection').find('input[type="radio"]:checked').prop('checked', false);
            $('.physicalActivitySection').find('input[type="checkbox"]:checked').prop('checked', false);
        } else {
            $('.physicalActivitySection').removeClass('d-none');
            $('.physicalActivitySection').find('input[type="checkbox"]:checked').prop('checked', false);

            var max = parseInt(pysicalElem.max);

            if (parseInt(pysicalElem.value) > max) {
                pysicalElem.value = max; 
            }
        }
    }
    window.showMedicationReason = function (medicationReason) {
        if ($(medicationReason).val() == "No") {
            $('.medication_reason').removeClass('d-none');
        } else {
            $('.medication_reason').addClass('d-none');
        }
    }
    window.tobaccoUsage = function (tobaccoUsage) {
        if ($(tobaccoUsage).val() == "Yes") {
            $('.quitting_tobacco').removeClass('d-none');
        } else {
            $('.quitting_tobacco').addClass('d-none');
        }
    }
    
    window.lastHbaResult = function (lastResult) {

        if($(lastResult).val() > 8.5){
            $('.eye_examination').removeClass('d-none');
        }else{
            $('.eye_examination').addClass('d-none');
        }
    }
    
    window.lastHbaDate = function (lastHbaDate) {
        let today = new Date(); 
        let mm = today. getMonth()+1; 
        const yyyy = today. getFullYear();
        todays_date = `${mm}/${yyyy}`
        console.log(todays_date - $(lastHbaDate).val())
    }    
    
    
    window.eyeExamination = function (eyeExamination) {
        if($(eyeExamination).val() == "Yes"){
            $('.eye_examination_detail').removeClass('d-none')
            $('.no_eye_examination').addClass('d-none')
            
        }else{
            $('.eye_examination_detail').addClass('d-none')
            $('.no_eye_examination').removeClass('d-none')
        }
    }
    
    window.diabeticNephropathy = function (diabeticNephropathy) {
        if($(diabeticNephropathy).val() == "Yes"){
            $('.diabetic_nephropathy_date').removeClass('d-none')
            $('.no_diabetic_nephropathy').addClass('d-none')
            
        }else{
            $('.diabetic_nephropathy_date').addClass('d-none')
            $('.no_diabetic_nephropathy').removeClass('d-none')
        }
    }
    window.nephropathyNotConducted = function (nephropathyNotConducted){
        console.log($(nephropathyNotConducted).val())
        if($(nephropathyNotConducted).val() == "none"){
            $('.nephropathy_options_none').removeClass('d-none')
        }else{
            $('.nephropathy_options_none').addClass('d-none')
        }
    }

    
    window.diabeticReport = function (diabeticReport) {
        console.log($(diabeticReport).val())
        if($(diabeticReport).val() == "No"){
            $('.diabetic_report_requested').removeClass('d-none')
        }else{
            $('.diabetic_report_requested').addClass('d-none')
        }
        
    }
    window.showHypercholestrolemiaOptions = function (hypercholestrolemia){
        if($(hypercholestrolemia).val() == "No"){
            $('.no_assesment').removeClass('d-none')
        }else{
            $('.no_assesment').addClass('d-none')
        }
    }
    window.haveEchodiogram = function (haveEchodiogram){
        console.log($(haveEchodiogram).val())
        if($(haveEchodiogram).val() == "No"){
            $('.no_echodiogram').removeClass('d-none')
        }else{
            $('.no_echodiogram').addClass('d-none')
        }
    }

    window.printValue = function (value){
        console.log($(value).val())
    }

    window.followUpWithCardiologist = function (cardiologist){
        console.log($(cardiologist).val());
        if($(cardiologist).val() == "Yes"){
            $('.follow_up_with_cardiologist').removeClass('d-none')
            $('.not_follow_up_with_cardiologist').addClass('d-none')
        }else{
            $('.follow_up_with_cardiologist').addClass('d-none')
            $('.not_follow_up_with_cardiologist').removeClass('d-none')
        }
    }

    window.hideAlcoholSection = function (alcoholElem) {
        if ($(alcoholElem).val() != "" && $(alcoholElem).val() == 0) {
            $('.alcoholSection').addClass('d-none')
            $('.alcoholSection').find('input[type="number"]').val('');
            $('.alcoholSection').find('input[type="radio"]:checked').prop('checked', false);
        } else {
            $('.alcoholSection').removeClass('d-none')
            
            var max = parseInt(alcoholElem.max);
    
            if (parseInt(alcoholElem.value) > max) {
                alcoholElem.value = max; 
            }
        }
    }


    /* Tobacco Use Section */
    window.smokingStatus = function () {
        var smoked_thirtydays = $('input[name="tobacco_use[smoked_in_thirty_days]"]:checked').val() == 'Yes' ? 'Yes' : 'No';
        var smoke_fifteenyears = $('input[name="tobacco_use[smoked_in_fifteen_years]"]:checked').val() == 'Yes' ? 'Yes' : 'No';
        var smokelessProduct = $('input[name="tobacco_use[smokeless_product_use]"]:checked').val() == 'Yes' ? 'Yes' : 'No';

        var averageUseinfo = (smoked_thirtydays == 'Yes' || smokelessProduct == 'Yes' || smoke_fifteenyears == 'Yes' ? true : false);

        var useTobacco = (smoked_thirtydays == 'No' && smokelessProduct == 'No' && smoke_fifteenyears == 'No' ? true : false);



        if (!useTobacco) {
            $('.quitSmokingquestion').removeClass('d-none')
        } else {
            $('.quitSmokingquestion').addClass('d-none')
            $('.quitSmokingquestion').find('input[type="radio"]:checked').prop('checked', false);


            $('.ldctquestion:visible').addClass('d-none')
        }

        if (averageUseinfo) {
            $('.average-smoking').removeClass('d-none');
        } else {
            $('.average-smoking').addClass('d-none');
            $('.average-smoking').find('input[type="number"]').val('');
            $('.average-smoking').find('input[type="radio"]:checked').prop('checked', false);
            quitTobaccoNo = $('input[name="tobacco_use[quit_tobacco]"]').eq(1);
            quitTobacco(quitTobaccoNo);
        }
    }

    window.claculateSmokingpacks = function () {
        var averagesmokingyear = parseFloat($('input[name="tobacco_use[average_smoking_years]"]').val());
        var averagepackperday = parseFloat($('input[name="tobacco_use[average_packs_per_day]"]').val());

        var totalPacks = averagesmokingyear * averagepackperday;
        $('input[name="tobacco_use[average_packs_per_year]"]').val(totalPacks);

        var tobaccoPatientAge = $('input[name="tobacco_use[patient_age]"]').val();

        if (totalPacks >= 30 && parseInt(tobaccoPatientAge) >= 50 && parseInt(tobaccoPatientAge) <= 80) {
            $('.ldctquestion').removeClass('d-none');
        } else {
            $('.ldctquestion').addClass('d-none');
            $('input[name="tobacco_use[average_packs_per_year]"]').val();
            $('input[name="tobacco_use[perform_ldct]"]:checked').prop('checked', false);
            var ldctRdio = $('input[name="tobacco_use[perform_ldct]"]').eq(1);
            ldctCouncelingSection(ldctRdio);
        }
    }

    window.quitTobacco = function (quitTobacco_elem) {
        if ($(quitTobacco_elem).val() == 'Yes') {
            $('.tobacoo-alternate').removeClass('d-none');
        } else {
            $('.tobacoo-alternate').addClass('d-none');
            $('.tobacoo-alternate').find('input[type="radio"]:checked').prop('checked', false);
        }
    }

    window.ldctCouncelingSection = function (radioElem) {
        if ($(radioElem).val() == "Yes") {
            $('.stepwizard-step.ldctcounseling').removeClass('d-none');
            var ldctStep = parseInt($('.stepwizard-step.ldctcounseling').children('a').text());
            $('.stepwizard-step').each(function (index, elem) {
                if (!$(elem).hasClass('ldctcounseling') && parseInt($(elem).find('a').text()) >= ldctStep) {
                    ldctStep = ldctStep + 1;
                    $(elem).children('a').text(ldctStep)
                }
            })

            var averagePacks = $('input[name="tobacco_use[average_packs_per_year]"]').val();
            $('input[name="ldct_counseling[no_of_packs_year]"]').val(averagePacks);
        } else {
            if ($('.ldctcounseling:visible').length > 0) {
                $('.stepwizard-step.ldctcounseling').addClass('d-none');
                var currentStepWiz = parseInt($('.stepwizard-step.tobaccouse').children('a').text());
                $('.stepwizard-step').each(function (index, elem) {
                    if (!$(elem).hasClass('ldctcounseling') && $(elem).children('a').text() == currentStepWiz + 2) {
                        currentStepWiz = currentStepWiz + 1;
                        $(elem).children('a').text(currentStepWiz)
                    }
                })

                $('.ldctcounseling').find('input[type="radio"]:checked').prop('checked', false);
                $('.ldctcounseling').find('input[name="ldct_counseling[current_quit_smoker]"]').val('');
            }
        }
    }

    window.autoFillage = function (patientElem, patientsArray) {
        var patientId = $(patientElem).val();
        var filterPatient = patientsArray.filter(function (val, index) {
            return val.id == patientId;
        })
        /* Adding age of selected patient inside the tobacco section age field */
        var patientAge = $('#patient_id').attr('patient_age', filterPatient['0'].age);
        $('input[name="tobacco_use[patient_age]"]').val(patientAge)
        
        $('input[name="tobacco_use[patient_age]"]').val(filterPatient['0'].age);

        if (patientId != '' && $(patientElem).hasClass('is-invalid')) {
            $(patientElem).removeClass('is-invalid')
        }
    }


    window.fallScreening = function (fallElem) {
        if ($(fallElem).val() == "Yes") {
            $('.agree_to_fall').removeClass('d-none');
        } else {
            $('.agree_to_fall').addClass('d-none');
            $('.agree_to_fall').find('input[type="radio"]:checked').prop('checked', false);
        }
    }

    window.showFluvaccineSection = function (fluvaccineElem) {
        if ($(fluvaccineElem).val() == 'Yes') {
            $('.flu_script:visible').addClass('d-none');
            $('.flu_script').find('input[type="radio"]:checked').prop('checked', false);
        } else {
            $('.flu_script').removeClass('d-none');
        }
    }
    window.showpnemuvaccineSection = function (pneumovaccineElem) {
        if ($(pneumovaccineElem).val() == 'Yes') {
            $('.script_pneumococcal:visible').addClass('d-none');
            $('.script_pneumococcal:visible').find('input[type="radio"]:checked').prop('checked', false);
        } else {
            $('.script_pneumococcal').removeClass('d-none');
        }
    }

    window.fluVaccineInformation = function (vaccineElem) {
        if ($(vaccineElem).val() == "Yes") {
            $('.fluvaccine_section').removeClass('d-none');

            $('.askFluVaccine').addClass('d-none');
            $('.askFluVaccine').find('input[type="radio"]:checked').prop('checked', false);

            $('.flu_script:visible').addClass('d-none');
            $('.flu_script').find('input[type="radio"]:checked').prop('checked', false);
        } else {
            $('.fluvaccine_section').addClass('d-none');
            $('.fluvaccine_section').find('input[type="text"]').val("");

            $('.askFluVaccine').removeClass('d-none');
        }
    }

    window.pneumococcalVaccineInformation = function (vaccineElem) {
        if ($(vaccineElem).val() == "Yes") {
            $('.pneumococcal_vaccine_section').removeClass('d-none');

            $('.askPneumococcalVaccine').addClass('d-none');
            $('.askPneumococcalVaccine').find('input[type="radio"]:checked').prop('checked', false);
            
            $('.script_pneumococcal').addClass('d-none');
            $('.script_pneumococcal').find('input[type="radio"]:checked').prop('checked', false);
        } else {
            $('.pneumococcal_vaccine_section').addClass('d-none');
            $('.pneumococcal_vaccine_section').find('input[type="radio"]:checked').prop('checked', false);
            $('.pneumococcal_vaccine_section').find('input[type="text"]').val('');

            $('.askPneumococcalVaccine').removeClass('d-none');
        }
    }


    window.askMammogram = function (mammogramElem) {
        if ($(mammogramElem).val() == 'Yes') {
            $('.mammogram_script:visible').addClass('d-none');
            $('.mammogram_script').find('input[type="radio"]:checked').prop('checked', false);
        } else {
            $('.mammogram_script').removeClass('d-none');
        }
    }

    window.refusedColfitguard = function (colonographElem) {
        if ($(colonographElem).val() == 'Yes') {
            $('.colonscopy_script:visible').addClass('d-none');
            $('.colonscopy_script').find('input[type="radio"]:checked').prop('checked', false);
        } else {
            $('.colonscopy_script').removeClass('d-none');
        }
    }


    window.mammogramInformation = function (mammoElem) {
        if ($(mammoElem).val() == "Yes") {
            $(".mammogramSection").removeClass('d-none');

            $(".mammogram_script:visible").addClass('d-none');
            $(".mammogram_script").find('input[type="radio"]:checked').prop('checked', false);

            $(".askMammogram").addClass('d-none');
            $(".askMammogram").find('input[type="radio"]:checked').prop('checked', false);
        } else {
            $(".mammogramSection").addClass('d-none');
            $(".mammogramSection").find('input[type="text"]').val("");
            $(".mammogramSection").find('input[type="checkbox"]:checked').prop('checked', false);

            $(".askMammogram").removeClass('d-none');
        }
    }

    window.colonoscopyInformation = function (colonoElem) {
        if ($(colonoElem).val() == "Yes") {
            $(".colonographSection").removeClass('d-none');

            $('.askColofitguard:visible').addClass('d-none');
            $(".askColofitguard").find('input[type="radio"]:checked').prop('checked', false);

            $('.colonscopy_script:visible').addClass('d-none');
            $(".colonscopy_script").find('input[type="radio"]:checked').prop('checked', false);

        } else {
            $(".colonographSection").addClass('d-none');
            $(".colonographSection").find('input[type="text"]').val("");
            $(".colonographSection").find('input[type="checkbox"]:checked').prop('checked', false);

            $(".askColofitguard").removeClass('d-none');

            udpateFieldsLable('', 1);
        }
    }

    window.diabeticpatientCheck = function (diabeticPatient_elem) {
        if ($(diabeticPatient_elem).val() == 'Yes') {
            $('.diabeted_hba1c_data').removeClass('d-none');
            $('.diabeted_hba1c_data').find('input[type="text"]').val('');
            $('.eye_examintaion').removeClass('d-none');
            $('.nephropathy').removeClass('d-none');

            $('.fbs_last12_moths:visible').addClass('d-none');
            $('.fbs_last12_moths').find('input[type="radio"]:checked').prop('checked', false);
            $('.fbs_last12_moths').find('input[type="checkbox"]:checked').prop('checked', false);
            $('.fbs_last12_moths').find('input[type="number"]').val('');
            $('.fbs_last12_moths').find('input[type="text"]').val('');
        } else {
            $('.diabeted_hba1c_data:visible').addClass('d-none');
            $('.diabetes_fbs_data:visible').addClass('d-none');
            $('.fbs_last12_moths').removeClass('d-none');

            $('.eye_examintaion:visible').addClass('d-none');
            $('.eye_examintaion').find('input[type="radio"]:checked').prop('checked', false);

            $('.eye_exam_repot:visible').addClass('d-none');
            $('.eye_exam_repot').find('input[type="radio"]:checked').prop('checked', false);


            $('.eye_exam_report_data:visible').addClass('d-none');
            $('.eye_exam_report_data').find('input[type="checkbox"]:checked').prop('checked', false);
            $('.eye_exam_report_data').find('input[type="text"]').val('');

            $('.eye_exam_order_section:visible').addClass('d-none');
            $('.eye_exam_order_section').find('input[type="checkbox"]:checked').prop('checked', false);


            $('.nephropathy').addClass('d-none');
            $('.nephropathy').find('input[type="radio"]:checked').prop('checked', false);

            $('.declined_urine_microalbumin:visible').addClass('d-none');
            $('.declined_urine_microalbumin').find('input[type="radio"]:checked').prop('checked', false);

            $('.urine_microalbumin_section:visible').addClass('d-none');
            $('.urine_microalbumin_section').find('input[type="radio"]:checked').prop('checked', false);
            $('.urine_microalbumin_section').find('input[type="text"]').val('');

            $('.ckd_stage4_data:visible').addClass('d-none');
            $('.ckd_stage4_data').find('input[type="radio"]:checked').prop('checked', false);
        }
    }

    window.diabeteseFbsdata = function (dibetesFbsElem) {
        if ($(dibetesFbsElem).val() == "Yes") {
            $('.diabetes_fbs_data').removeClass('d-none');
        } else {
            $('.diabetes_fbs_data').addClass('d-none');
            $('.diabetes_fbs_data').find('input[type="number"]').val('');
            $('.diabetes_fbs_data').find('input[type="text"]').val('');
            $('.diabeted_hba1c_data:visible').addClass('d-none')
            $('.diabeted_hba1c_data:visible').find('input[type="text"]').val('');


            $('.eye_examintaion').addClass('d-none');
            $('.eye_examintaion').find('input[type="radio"]:checked').prop('checked', false);

            $('.eye_exam_order_section').addClass('d-none');
            $('.eye_exam_order_section').find('input[type="radio"]:checked').prop('checked', false);

            $('.eye_exam_repot').addClass('d-none');
            $('.eye_exam_repot').find('input[type="radio"]:checked').prop('checked', false);

            $('.eye_exam_report_data').addClass('d-none');
            $('.eye_exam_report_data').find('input[type="text"]').val('');
            $('.eye_exam_report_data').find('input[type="checkbox"]:checked').prop('checked', false);

            $('.nephropathy').addClass('d-none');
            $('.nephropathy').find('input[type="radio"]:checked').prop('checked', false);

            $('.urine_microalbumin_section').addClass('d-none');
            $('.urine_microalbumin_section').find('input[type="text"]').val('');
            $('.urine_microalbumin_section').find('input[type="checkbox"]:checked').prop('checked', false);

            $('.declined_urine_microalbumin').addClass('d-none');
            $('.declined_urine_microalbumin').find('input[type="radio"]:checked').prop('checked', false);

            $('.ckd_stage4_data').addClass('d-none');
            $('.ckd_stage4_data').find('input[type="radio"]:checked').prop('checked', false);
        }
    }

    window.diabetesHba1cdata = function (dibetesHba1cElem) {
        if ($(dibetesHba1cElem).hasClass('fbsvalue')) {
            if ($(dibetesHba1cElem).val() > 100) {
                $('.diabeted_hba1c_data').removeClass('d-none');
            } else {
                $('.diabeted_hba1c_data').addClass('d-none');
                $('.diabeted_hba1c_data').find('input[type="text"]').val('');
            }
        }

        if ($(dibetesHba1cElem).hasClass('hba1cvalue')) {
            $dmtype2_active = $('input[name="diabetes[diabetec_patient]"]:checked').val();
            if ($dmtype2_active == 'No') {
                if ($(dibetesHba1cElem).val() >= 6.5) {
                    $('.eye_examintaion').removeClass('d-none');
                    $('.nephropathy').removeClass('d-none');
                } else {
                    $('.eye_examintaion').addClass('d-none');
                    $('.eye_exam_repot:visible').addClass('d-none');
                    $('.eye_exam_report_data:visible').addClass('d-none');
                    $('.nephropathy').addClass('d-none');
                    $('.nephropathy').find('input[type="radio"]:checked').prop('checked', false);

                    $('.eye_examintaion').find('input[name="diabetes[diabetec_eye_exam]"]:checked').prop('checked', false);
                    $('.eye_exam_repot').find('input[name="diabetes[diabetec_eye_exam_report]"]:checked').prop('checked', false);
                    $('.eye_exam_report_data').find('input[type="text"]').val('');
                    $('.eye_exam_report_data').find('input[type="radio"]:checked').prop('checked', false);
                    $('.eye_exam_report_data').find('input[type="checkbox"]:checked').prop('checked', false);


                    $('.urine_microalbumin_section:visible').addClass('d-none');
                    $('.urine_microalbumin_section').find('input[type="text"]').val('');
                    $('.urine_microalbumin_section').find('input[type="radio"]:checked').prop('checked', false);

                    $('.declined_urine_microalbumin:visible').addClass('d-none');
                    $('.declined_urine_microalbumin').find('input[type="radio"]:checked').prop('checked', false);
                }
            }
        }
    }

    window.eyeExamReport = function name(eyeExamreport_elem) {
        if ($(eyeExamreport_elem).val() == 'Yes') {
            $('.eye_exam_repot').removeClass('d-none');
            $('.eye_exam_order_section').addClass('d-none');
            $('.eye_exam_order_section').find('input[type="radio"]:checked').prop('checked', false);
        } else {
            $('.eye_exam_repot').addClass('d-none');
            $('.eye_exam_repot').find('input[type="radio"]:checked').prop('checked', false);

            $('.eye_exam_report_data').addClass('d-none');
            $('.eye_exam_report_data').find('input[type="radio"]:checked').prop('checked', false);
            var eyeExamreportRadio = $('.eye_exam_report_data').find('input[type="radio"]').eq(2);
            eyeExamReportData(eyeExamreportRadio);


            $('.eye_exam_order_section').removeClass('d-none');

        }
    }

    window.eyeExamReportData = function name(eyeExamreportdata_elem) {
        if ($(eyeExamreportdata_elem).val() == 'report_available') {
            $('.eye_exam_report_data').removeClass('d-none');
        } else {
            $('.eye_exam_report_data').addClass('d-none');
            $('.eye_exam_report_data').find('input[type="text"]').val('');
            $('.eye_exam_report_data').find('input[type="radio"]:checked').prop('checked', false);
            $('.eye_exam_report_data').find('input[type="checkbox"]:checked').prop('checked', false);
        }
    }

    window.checkDiabetecRatipathy = function name(dibetec_ratiopathy_elem) {
        if ($(dibetec_ratiopathy_elem).is(":checked") == true) {
            $('.diabetec_retinopath').removeClass('d-none');
        } else {
            $('.diabetec_retinopath').addClass('d-none');
            $('.diabetec_retinopath').find('input[name="diabetes[diabetec_ratinopathy]"]:checked').prop('checked', false);
        }
    }

    window.urineMicroalbumin = function name(urineMicroalbumin_elem) {
        if ($(urineMicroalbumin_elem).val() == 'Yes') {
            $('.urine_microalbumin_section').removeClass('d-none');
            $('.declined_urine_microalbumin:visible').addClass('d-none');
            $('.declined_urine_microalbumin').find('input[type="radio"]:checked').prop('checked', false);
            $ckedRadio = $('.declined_urine_microalbumin').find('input[type="radio"]').eq(2);
            inhibitorsData($ckedRadio);
        } else {
            $('.urine_microalbumin_section').addClass('d-none');
            $('.declined_urine_microalbumin').removeClass('d-none');
            $('.urine_microalbumin_section').find('input[type="text"]').val('');
            $('.urine_microalbumin_section').find('input[type="radio"]:checked').prop('checked', false);
        }
    }

    window.inhibitorsData = function name(inhibitors_elem) {
        if ($(inhibitors_elem).val() == 'none') {
            $('.ckd_stage4_data').removeClass('d-none');
        } else {
            $('.ckd_stage4_data').addClass('d-none');
            $('.ckd_stage4_data').find('input[type="radio"]:checked').prop('checked', false);
        }
    }



    /* Cholesterol section Function */
    window.showLdlandASVDsection = function (thiselem) {
        if ($(thiselem).val() == 'Yes') {
            $('.ldlvalues_section').removeClass('d-none');
        } else {
            $('.ldlvalues_section').addClass('d-none');

            $('.ldlvalues_section').find('input[type="text"]').val('');
        }
    }


    window.showStatinOrHypercholSection = function (ascvdElem) {
        var ascvdValue = $(ascvdElem).val();
        prescribedStatin(ascvdValue, 1);
        if (ascvdValue == 'Yes') {
            $('.hypercholesterolemia_section').addClass('d-none');
            $('.hypercholesterolemia_section').find('input[type="radio"]:checked').prop('checked', false);
        } else {
            $('.hypercholesterolemia_section').removeClass('d-none');

            /* Hiding reason for no statin section */
            $('.medical_reasonforstatin_section:visible').addClass('d-none');
            $('.medical_reasonforstatin_section').find('input[type="checkbox"]:checked').prop('checked', false);
        }
    }


    window.showDibetesOrStatin = function () {
        var hyperLDLvalue = $('input[name="cholesterol_assessment[ldlvalue_190ormore]"]:checked').val();
        var hyperCholesterol = $('input[name="cholesterol_assessment[pure_hypercholesterolemia]"]:checked').val();

        showStatin = (hyperLDLvalue == 'Yes' || hyperCholesterol == 'Yes' ? 'Yes' : 'No');
        prescribedStatin(showStatin, 1);

        showDiabetesSection = (hyperLDLvalue == 'No' && hyperCholesterol == 'No' ? true : false);
        if (showDiabetesSection) {
            $('.cholesterol_diabetes_section').removeClass('d-none');

            /* Hiding reason for no statin section */
            $('.medical_reasonforstatin_section:visible').addClass('d-none');
            $('.medical_reasonforstatin_section').find('input[type="checkbox"]:checked').prop('checked', false);
        } else {
            $('.cholesterol_diabetes_section').addClass('d-none');
            $('.cholesterol_diabetes_section').find('input[type="radio"]:checked').prop('checked', false);
        }
    }


    window.askPatientage = function (diabetecPatientelem) {
        if ($(diabetecPatientelem).val() == 'Yes') {
            $('.patient_agesection').removeClass('d-none')
        } else {
            $('.patient_agesection').addClass('d-none');
            $('.patient_agesection').find('input[type="radio"]:checked').prop('checked', false);

            $('.last_two_yearsLDL').addClass('d-none');
            $('.last_two_yearsLDL').find('input[type="radio"]:checked').prop('checked', false);

            $('.statin_question_section:visible').addClass('d-none');
            $('.statin_question_section').find('input[type="radio"]:checked').prop('checked', false);

            $('.statin_dosage_section:visible').addClass('d-none');
            $('.statin_dosage_section option:selected').prop('selected', false);

            $('.medical_reasonforstatin_section:visible').addClass('d-none');
            $('.medical_reasonforstatin_section').find('input[type="checkbox"]:checked').prop('checked', false);

        }
    }


    window.lastTwoyearsStatin = function (patientageElem) {
        if ($(patientageElem).val() == 'Yes') {
            $('.last_two_yearsLDL').removeClass('d-none');
        } else {
            $('.last_two_yearsLDL').addClass('d-none');
            $('.last_two_yearsLDL').find('input[type="radio"]:checked').prop('checked', false);

            $('.statin_question_section:visible').addClass('d-none');
            $('.statin_question_section').find('input[type="radio"]:checked').prop('checked', false);

            $('.statin_dosage_section:visible').addClass('d-none');
            $('.statin_dosage_section option:selected').prop('selected', false);

            $('.medical_reasonforstatin_section:visible').addClass('d-none');
            $('.medical_reasonforstatin_section').find('input[type="checkbox"]:checked').prop('checked', false);
        }
    }


    window.prescribedStatin = function (forstatinElem, mainelem) {
        if (forstatinElem == 'Yes') {
            $('.statin_question_section').removeClass('d-none');
        } else {
            $('.statin_question_section').addClass('d-none');
            $('.statin_question_section').find('input[type="radio"]:checked').prop('checked', false);

            /* Hiding statin dosage section */
            $('.statin_dosage_section').addClass('d-none');
            $('.statin_dosage_section option:selected').prop('selected', false);
        }

        patientonStatin = (forstatinElem == 'No' && mainelem == 1 ? 'Yes' : forstatinElem)
        statinMedicalReason()
    }


    window.showStatinDosage = function (statinPrescribedelem) {
        if ($(statinPrescribedelem).val() == 'Yes') {
            $('.statin_dosage_section').removeClass('d-none');
        } else {
            $('.statin_dosage_section').addClass('d-none');
            $('.statin_dosage_section option:selected').prop('selected', false);
        }
        statinMedicalReason(statinPrescribedelem);
    }



    function statinMedicalReason(statinElem) {
        if ($(statinElem).val() == 'No') {
            $('.medical_reasonforstatin_section').removeClass('d-none');
        } else {
            $('.medical_reasonforstatin_section:visible').addClass('d-none');
            $('.medical_reasonforstatin_section').find('input[type="checkbox"]:checked').prop('checked', false);
        }
    }



    function formValidation(curInputs) {
        isValid = true;
        $(".form-control").removeClass("border border-danger");
        for (var i = 0; i < curInputs.length; i++) {
            if (curInputs[i].value == "") {
                isValid = false;
                $(curInputs[i]).closest(".form-control").addClass("border border-danger");
            }
        }

        if ($('#patient_id').val() == '') {
            $('#patient_id').addClass('is-invalid');
            toastr.error('Select Patient');
            isValid = false;
        } else {
            $('#patient_id').removeClass('is-invalid');
        }
        if ($('#program_id').val() == '') {
            $('#program_id').addClass('is-invalid');
            toastr.error('Select Program');
            isValid = false;
        } else {
            $('#program_id').removeClass('is-invalid');
        }

        return isValid;
    }

    window.exerciseTypeForm = function (curElem) {
        $('#exerciseType').show();
        if ($(curElem).is(":checked") == true) {
            $(curElem).prop('checked', true);
            $(curElem).val('1');
            $('input.tab1[type="number"]').val("");
            $('input.tab1[type="radio"]').prop("checked", false);
        } else {
            $('#exerciseType').find('input[type="radio"]').val("");
            $('#exerciseType').find('input[type="radio"]').prop('checked', false);
        }
    }

    /* Set the width of the sidebar to 250px and the left margin of the page content to 250px */
    window.openNav = function () {
        document.getElementById("mySidebar").style.width = "250px";
        document.getElementById("main").style.marginLeft = "250px";
    }

    /* Set the width of the sidebar to 0 and the left margin of the page content to 0 */
    window.closeNav = function () {
        document.getElementById("mySidebar").style.width = "0";
        document.getElementById("main").style.marginLeft = "0";
    }

    window.udpateFieldsLable = function (testElem) {
        if (testElem != '') {
            var testName = $(testElem).val();
            $('.performed_on').find('label').text(testName + ' done on');
            $('.performed_on').find('input[type="text"]:first').attr('placeholder', testName + ' done on');
            $('.performed_on').find('input[type="text"]:first').val('');

            $('.performed_at').find('label').text(testName + ' done at');
            $('.performed_at').find('input[type="text"]:first').attr('placeholder', testName + ' done at');
            $('.performed_at').find('input[type="text"]:first').val('');

            $('.next_perform').find('label').text('Next ' + testName + ' due on');
            $('.next_perform').find('input[type="text"]:first').attr('placeholder', 'Next' + testName + ' due on');
            $('.next_perform').find('input[type="text"]:first').val('');
        } else {
            $('.performed_on').find('label').text('Colonoscopy / FIT Test / Cologuard done on')
            $('.performed_on').find('input[type="text"]:first').attr('placeholder', 'Colonoscopy / FIT Test / Cologuard done on')

            $('.performed_at').find('label').text('Colonoscopy / FIT Test / Cologuard done at')
            $('.performed_at').find('input[type="text"]:first').attr('placeholder', 'Colonoscopy / FIT Test / Cologuard done at ')

            $('.next_perform').find('label').text('Next Colonoscopy / FIT Test / Cologuard due on')
            $('.next_perform').find('input[type="text"]:first').attr('placeholder', 'Next Colonoscopy / FIT Test / Cologuard due on')
        }
    }


    /*Rizwan Start data*/
    window.show_Adls = function () {
        var a = $('input[name="caregiver_assessment[every_day_activities]"]:checked').val();
        var b = $('input[name="caregiver_assessment[medications]"]:checked').val();
        console.log(a);
        if (a == 'Yes' || b == 'Yes') {

            $('.adls_section').removeClass('d-none');
        } else {
            $('.adls_section').addClass('d-none');
            $('.adls_section').find('input[type="radio"]').prop('checked', false);
            $('.adls_section_No').addClass('d-none');
            $('.adls_section_No').find('input[type="radio"]').prop('checked', false);
            $('.Live_the_patient_section').addClass('d-none');
            $('.Live_the_patient_section').find('input[type="radio"]').prop('checked', false);
        }
    }
    

    window.adls_section_Yes = function (ltp) {

        if ($(ltp).val() == 'Yes') {
            $('.Live_the_patient_section').removeClass('d-none');


            $('.adls_section_No').addClass('d-none');
            $('.adls_section_No').find('input[type="radio"]').prop('checked', false);

        } else {
            $('.Live_the_patient_section').find('input[type="text"]').val('');
            $('.Live_the_patient_section').addClass('d-none');



            $('.adls_section_No').removeClass('d-none');
        }

    }
    window.other_Provider_beside_PCP_Yes = function () {
        var a = $('input[name="other_Provider[other_provider_beside_pcp]"]:checked').val();
        //var b =$('input[name="caregiver_assessment[medications]"]:checked').val();
        console.log(a);
        if (a == 'Yes') {
            $('.other_Provider_beside_PCP_section').removeClass('d-none');
        } else {
            $('.other_Provider_beside_PCP_section').addClass('d-none');
            $('.other_Provider_beside_PCP_section').find('input[type="radio"]').prop('checked', false);
        }
    }

    window.askaboutNutritionist = function (bmiField) {
        if ($(bmiField).val() >= 30) {
            $('.askForNutritionist').removeClass('d-none');
        } else {
            $('.askForNutritionist:visible').addClass('d-none');
            $('.askForNutritionist:visible').find('input[type="radio"]:checked').prop('checked', false);
        }
    }
    

    window.addnewRow = function (btnElem) {
        var mainDiv = $(btnElem).parents('.container:first');
        var totalRows = $(mainDiv).find('.row.form-group').length+1;

        var newRow_html =   `<div class="row form-group mb-3">
                                <div class="row col-3 ms-2">
                                    <input type="number" min="0" class="form-control" name="hypertension[bp_day_`+totalRows+`]" placeholder="Date" value="" />
                                </div>

                                <div class="row col-3 ms-2">
                                    <input type="text" class="form-control start_date" name="hypertension[systolic_day_`+totalRows+`]" placeholder="Systolic" value="" />
                                </div>

                                <div class="row col-3 ms-2">
                                    <input type="text" class="form-control start_date" name="hypertension[diastolic_day_`+totalRows+`]" placeholder="Diastolic" value="" />
                                </div>
                                
                                <div class="row col-2 ms-2">
                                    <div class="pull-left align-items-end col-3">
                                        <button class="btn btn-danger mx-2" type="button">X</button>
                                    </div>
                                </div>
                            </div>`;

        /* Adding row */
        $(newRow_html).insertBefore($(btnElem).parents('.row:first'));
    }
    
    window.removethisRow = function (btnElem) {
        $(btnElem).parents('.row.form-group').remove();
    }


    window.show_newBmi = function(obesity_weight_elem) {
        if ($(obesity_weight_elem).parents('.col:first').hasClass('lost_weight') == false) {
            if ($(obesity_weight_elem).val() == "Yes") {
                $('.obesity_newbmi').removeClass('d-none');

                $('.lost_weight:visible').addClass('d-none');
                $('.lost_weight').find('input[type="radio"]:checked').prop('checked', false);
            } else {
                $('.lost_weight').removeClass('d-none');

                $('.obesity_newbmi').addClass('d-none');
                $('.obesity_newbmi').find('input:first').val('');
            }
        } else {
            if ($(obesity_weight_elem).val() == "Yes") {
                $('.obesity_newbmi').removeClass('d-none');
            } else {
                $('.obesity_newbmi').addClass('d-none');
                $('.obesity_newbmi').find('input:first').val('');
            }
        }
    }


    window.assessment_score = function (score_elem) {
        $(score_elem).parents('td:first').siblings('th[scope="row"]').find('input:first').val($(score_elem).val())

        var totalScore = 0;
        $('.scoreinput').each(function (index, item) {
            totalScore += parseInt($(item).val())
        })

        $('input[name="copd_assessment[total_assessment_score]"]').val(totalScore)
    }
});
