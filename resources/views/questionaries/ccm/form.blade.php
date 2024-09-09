@if (!empty($list['diagnosis']))
    @include('questionaries.ccm.stepper', ['sections' => $list['diagnosis']])
@else
    @include('questionaries.ccm.stepper')
@endif

<div class="row my-2">
    <div class="col-12">

        {{-- PHYSICAL HEALTH-FALL SCREENING START --}}
        @include('questionaries.sections.physical-health-fall-screen',['row'=> $list['fall_screening'] ?? [],'stepNo'=>1])
        {{-- PHYSICAL HEALTH-FALL SCREENING END --}}

        {{-- DEPRESSION PHQ-9 START --}}
        @include('questionaries.sections.depression-phq9',['row'=> $list['depression_phq9'] ?? [],'stepNo'=>2])
        {{-- DEPRESSION PHQ-9 END --}}

        {{-- COGNITIVE ASSESSMENT START --}}
        @include('questionaries.sections.cognitive-assessment',['row'=> $list['cognitive_assessment'] ?? [],'stepNo'=>3])
        {{-- COGNITIVE ASSESSMENT END --}}

        {{-- CareGiver START --}}
        @include('questionaries.sections.caregiver-assessment',['row'=> $list['caregiver_assessment'] ?? [],'stepNo'=>4])
        {{-- CareGiver END --}}

        {{-- Other Providers START --}}
        @include('questionaries.sections.other-providers',['row'=> $list['other_provider'] ?? [],'stepNo'=>5])
        {{-- Other Providers END --}}

        {{-- IMMUNIZATION START --}}
        @include('questionaries.sections.immunization',['row'=> $list['immunization'] ?? [],'stepNo'=>6])
        {{-- IMMUNIZATION END --}}

        {{-- SCREENING START --}}
        @include('questionaries.sections.screening',['row'=> $list['screening'] ?? [],'stepNo'=>7])
        {{-- SCREENING END --}}

        {{-- GENERAL ASSESSMENT START --}}
        @include('questionaries.sections.general-assessment',['row'=> $list['general-assessment'] ?? [],'stepNo'=>8])
        {{-- GENERAL ASSESSMENT END --}}

        @include('questionaries.sections.hypercholesterolemia',['row'=> $list['hypercholesterolemia'] ?? [],'stepNo'=>9])
        @if (in_array('hypercholesterolemia', $list['diagnosis']))    
            {{-- HYPERCHOLESTEROLEMIA START --}}
            {{-- HYPERCHOLESTEROLEMIA END --}}
        @endif

        @include('questionaries.sections.ga_diabetes',['row'=> $list['diabetes_mellitus'] ?? [],'stepNo'=>10])
        @if (in_array('diabetes_mellitus', $list['diagnosis']))    
            {{-- DIABETES START --}}
            {{-- DIABETES END --}}
        @endif

        @include('questionaries.sections.copd-assessment',['row'=> $list['copd_assessment'] ?? [],'stepNo'=>11])
        @if (in_array('chronic_obstructive_pulmonary_disease', $list['diagnosis']))    
            {{-- COPD START --}}
            {{-- COPD END --}}
        @endif

        @include('questionaries.sections.ckd-assesment',['row'=> $list['ckd_assesment'] ?? [],'stepNo'=>12])
        @if (in_array('chronic_kidney_disease', $list['diagnosis']))    
            {{-- CKD START --}}
            {{-- CKD END --}}
        @endif

        @include('questionaries.sections.hypertension',['row'=> $list['hypertension'] ?? [],'stepNo'=>13])
        @if (in_array('hypertension', $list['diagnosis']))    
            {{-- HYPERTENSION START --}}
            {{-- HYPERTENSION END --}}
        @endif

        @include('questionaries.sections.obesity',['row'=> $list['obesity'] ?? [],'stepNo'=>14])
        @if (in_array('obesity', $list['diagnosis']))    
            {{-- OBESITY START --}}
            {{-- OBESITY END --}}
        @endif

        @include('questionaries.sections.congestive_heart_failure',['row'=> $list['congestive_heart_failure'] ?? [],'stepNo'=>15,'end'=>true])
        @if (in_array('congestive_heart_failure', $list['diagnosis']))    
            {{-- CONGESTIVE HEART FAILURE START --}}
            {{-- CONGESTIVE HEART FAILURE END --}}
        @endif

    </div>
</div>

<script type="text/javascript">
    setTimeout(function() {
        $('.start_date').datepicker({
            format: "mm/dd/yyyy",
            startView: "days",
            minViewMode: "days",
            orientation: "bottom auto",
            autoclose: true
        });
        $('.end_date').datepicker({
            format: "mm/dd/yyyy",
            startView: "days",
            minViewMode: "days",
            orientation: "bottom auto",
            autoclose: true
        });
        $('.datepicker').datepicker({
            format: "mm/yyyy",
            startView: "months",
            minViewMode: "months"
        }).on('changeDate', function(e) {
            var options = {
                year: 'numeric',
                month: '2-digit'
            };
            /* Next mammogram date set */
            if ($(this).attr('name') == 'diabetes[result_month]') {
                var enteredDate = $(this).val().split('/');
                var enteredDate = enteredDate[0] + "/01/" + enteredDate[1]
                var formattedDate = new Date(enteredDate).getTime()
                var now = new Date();
                var sixMonthBeforeNow = new Date(now).setMonth(now.getMonth() - 6);
                if (formattedDate > sixMonthBeforeNow) {
                    console.log("date entered is less than 6 months");
                }
                // if date entered is over six months from today
                if (formattedDate < sixMonthBeforeNow) {
                    console.log("date entered is greater than 6 months");
                }
                // var result = todays_date - formattedDate
                // console.log(todays_date, formattedDate)
                // var d = new Date();
                // var todaysDate = d.toLocaleString();
                // d.setDate(d.getDate() - 5);

                // document.write('<br>5 days ago was: ' + d.toLocaleString());
            }
        })

        $('.datepicker_simple').datepicker({
            format: "mm/yyyy",
            startView: "months",
            minViewMode: "months"
        })
    }, 100)
</script>