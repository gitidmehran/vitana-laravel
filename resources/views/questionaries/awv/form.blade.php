@include('questionaries.awv.stepper')
<div class="row my-2">
    <div class="col-12">

        {{-- PHYSICAL HEALTH-FALL SCREENING START --}}
        @include('questionaries.sections.physical-health-fall-screen',['row'=> $list['fall_screening'] ?? [],'stepNo'=>1])
        {{-- PHYSICAL HEALTH-FALL SCREENING END --}}

        {{-- DEPRESSION PHQ-9 START --}}
        @include('questionaries.sections.depression-phq9',['row'=> $list['depression_phq9'] ?? [],'stepNo'=>2])
        {{-- DEPRESSION PHQ-9 END --}}

        {{-- HIGH STRESS START --}}
        @include('questionaries.sections.high-stress',['row'=> $list['high_stress'] ?? [],'stepNo'=>3])
        {{-- HIGH STRESS END --}}
        
        {{-- GENERAL HEALTH START --}}
        @include('questionaries.sections.general-health',['row'=> $list['general_health'] ?? [],'stepNo'=>4])
        {{-- GENERAL HEALTH END --}}
        
        {{-- SOCIAL/EMOTIONAL START --}}
        @include('questionaries.sections.social-emotional-support',['row'=> $list['social_emotional_support'] ?? [],'stepNo'=>5])
        {{-- SOCIAL/EMOTIONAL END --}}
        
        {{-- PAIN START --}}
        @include('questionaries.sections.pain',['row'=> $list['pain'] ?? [],'stepNo'=>6])
        {{-- PAIN END --}}

        {{-- COGNITIVE ASSESSMENT START --}}
        @include('questionaries.sections.cognitive-assessment',['row'=> $list['cognitive_assessment'] ?? [],'stepNo'=>7])
        {{-- COGNITIVE ASSESSMENT END --}}
        
        {{-- PHYSICAL ACTIVITIES START --}}
        @include('questionaries.sections.physical-activities',['row'=> $list['physical_activities'] ?? [],'stepNo'=>8])
        {{-- PHYSICAL ACTIVITIES END --}}

        {{-- ALCOHOL USE START --}}
        @include('questionaries.sections.alcohol-use',['row'=> $list['alcohol_use'] ?? [],'stepNo'=>9])
        {{-- ALCOHOL USE END --}}
        
        {{-- TOBACCO USE START --}}
        @include('questionaries.sections.tobacco-use',['row'=> $list['tobacco_use'] ?? [],'stepNo'=>10])
        {{-- TOBACCO USE END --}}

        {{-- LDCT COUNSELING START --}}
        @include('questionaries.sections.ldct-counseling',['row'=> $list['ldct_counseling'] ?? [],'stepNo'=>11])
        {{-- LDCT COUNSELING END --}}
        
        {{-- NUTRITION USE START --}}
        @include('questionaries.sections.nutrition',['row'=> $list['nutrition'] ?? [],'stepNo'=>12])
        {{-- NUTRITION USE END --}}

        {{-- SEAT BELT USE START --}}
        @include('questionaries.sections.seat-belt-use',['row'=> $list['seatbelt_use'] ?? [],'stepNo'=>13])
        {{-- SEAT BELT USE END --}}
        
        {{-- IMMUNIZATION START --}}
        @include('questionaries.sections.immunization',['row'=> $list['immunization'] ?? [],'stepNo'=>14])
        {{-- IMMUNIZATION END --}}
        
        {{-- SCREENING START --}}
        @include('questionaries.sections.screening',['row'=> $list['screening'] ?? [],'stepNo'=>15])
        {{-- SCREENING END --}}
        
        {{-- DIABATES START --}}
        @include('questionaries.sections.diabetes',['row'=> $list['diabetes'] ?? [],'stepNo'=>16])
        {{-- DIABATES END --}}

        {{-- CHOLESTEROL ASSESSMENT STARTS --}}
        @include('questionaries.sections.cholesterol-assessment',['row'=> $list['cholesterol_assessment'] ?? [],'stepNo'=>17])
        {{-- CHOLESTEROL ASSESSMENT END --}}
        
        {{-- BP ASSESSMENT START --}}
        @include('questionaries.sections.bp-assessment',['row'=> $list['bp_assessment'] ?? [],'stepNo'=>18])
        {{-- BP ASSESSMENT END --}}
        
        {{-- Weight ASSESSMENT START --}}
        @include('questionaries.sections.weight-assessment',['row'=> $list['weight_assessment'] ?? [],'stepNo'=>19])
        {{-- Weight ASSESSMENT END --}}
        
        {{-- PHYSICAL EXAM START --}}
        @include('questionaries.sections.physical-exam',['row'=> $list['physical_exam'] ?? [],'stepNo'=>20])
        {{-- PHYSICAL EXAM END --}}
        
        {{-- Miscellaneous START --}}
        @include('questionaries.sections.miscellaneous',['row'=> $list['miscellaneous'] ?? [],'stepNo'=>21])
        {{-- Miscellaneous END --}}
    </div>
</div>

<script type="text/javascript">
    if (window.location.pathname.indexOf('edit') === -1) {
        $(document).ready(function(){
            /* Adding age of selected patient inside the tobacco section age field */
            var patientAge = $('#patient_id').attr('patient_age')
            $('input[name="tobacco_use[patient_age]"]').val(patientAge);
        });
    }
    setTimeout(function () {
        $('.datepicker').datepicker({
                format: "mm/yyyy",
                startView: "months",
                minViewMode: "months"
            }).on('changeDate', function(e) {
                var options = { year: 'numeric', month: '2-digit' };
                /* Next mammogram date set */
                if ($(this).attr('name') == 'screening[mammogram_done_on]') {
                    var mammograDate = new Date(e.date.setMonth(e.date.getMonth()+27));
                    var next_mammogram = mammograDate.toLocaleDateString("en-US", options)
                    $('input[name="screening[next_mommogram]"]').val(next_mammogram);
                }

                /*Next Colonoscopy, Cologuard, Fit test dateset */
                if ($(this).attr('name') == 'screening[colonoscopy_done_on]') {
                    console.log($(this).attr('name'), $('input[name="screening[colon_test_type]"]:checked').val());
                    if ($('input[name="screening[colon_test_type]"]:checked').val() == 'Colonoscopy') {
                        
                        var colonoscopyDate = new Date(e.date.setMonth(e.date.getMonth()+120));
                        var next_colonoscopy = colonoscopyDate.toLocaleDateString("en-US", options)
                        $('input[name="screening[next_colonoscopy]"]').val(next_colonoscopy);
                        
                    } else if ($('input[name="screening[colon_test_type]"]:checked').val() == 'Fit Test') {
                        
                        var fit_testDate = new Date(e.date.setMonth(e.date.getMonth()+12));
                        console.log('Hello', fit_testDate);
                        var next_fit_test = fit_testDate.toLocaleDateString("en-US", options)
                        $('input[name="screening[next_colonoscopy]"]').val(next_fit_test);

                    } else if ($('input[name="screening[colon_test_type]"]:checked').val() == 'Cologuard') {

                        var cologuardDate = new Date(e.date.setMonth(e.date.getMonth()+24));
                        var next_cologuard = cologuardDate.toLocaleDateString("en-US", options)
                        $('input[name="screening[next_colonoscopy]"]').val(next_cologuard);

                    }
                }
            });
    }, 100);


    
</script>
