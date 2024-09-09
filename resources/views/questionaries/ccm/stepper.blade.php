<div class="row my-2">
    <div class="stepwizard" row='1'>
        <div class="stepwizard-row setup-panel">
            <div class="stepwizard-step">
                <a href="#step-1" type="button" class="btn btn-primary btn-circle" disabled="disabled" onclick="handleStepChange(1);">1</a>
                <p>Physical Health-Fall Screening</p>
            </div>

            <div class="stepwizard-step">
                <a href="#step-2" type="button" class="btn btn-default bg-secondary btn-circle" disabled="disabled" onclick="handleStepChange(2);">2</a>
                <p>Depression PHQ-9</p>
            </div>

            <div class="stepwizard-step">
                <a href="#step-3" type="button" class="btn btn-default bg-secondary btn-circle" disabled="disabled" onclick="handleStepChange(3);">3</a>
                <p>Cognitive Assesment</p>
            </div>

            <div class="stepwizard-step">
                <a href="#step-4" type="button" class="btn btn-primary bg-secondary btn-circle" disabled="disabled" onclick="handleStepChange(4);">4</a>
                <p>Caregiver Assessment</p>
            </div>

            <div class="stepwizard-step">
                <a href="#step-5" type="button" class="btn btn-default bg-secondary btn-circle" disabled="disabled" onclick="handleStepChange(5);">5</a>
                <p>Other Providers</p>
            </div>

            <div class="stepwizard-step">
                <a href="#step-6" type="button" class="btn btn-default bg-secondary btn-circle" disabled="disabled" onclick="handleStepChange(6);">6</a>
                <p>Immunization</p>
            </div>

            <div class="stepwizard-step">
                <a href="#step-7" type="button" class="btn btn-default bg-secondary btn-circle" disabled="disabled" onclick="handleStepChange(7);">7</a>
                <p>Screening</p>
            </div>
        </div>
    </div>

    <div class="stepwizard" row='2'>
        <div class="stepwizard-row setup-panel">
            <div class="stepwizard-step">
                <a href="#step-8" type="button" class="btn btn-default bg-secondary btn-circle" disabled="disabled" onclick="handleStepChange(8);">8</a>
                <p>General Assessment</p>
            </div>

            @if (!empty($sections))    
                @php
                    $step = 8;
                @endphp
                @foreach ($sections as $item)
                    @php
                        $dynamic_step = Config('constants.diseases_steps')[$item] ?? '';
                        
                        if ($dynamic_step != '') {
                            $step = $step+1;
                        }
                    @endphp

                    @if ($dynamic_step != '')    
                        <div class="stepwizard-step">
                            <a href="#step-{{$dynamic_step}}" type="button" class="btn btn-default bg-secondary btn-circle" disabled="disabled" onclick="handleStepChange({{$dynamic_step}});">{{$step}}</a>
                            <p>{{ucwords(str_replace('_', ' ', $item))}}</p>
                        </div>
                    @endif
                @endforeach
            @endif


            

            {{-- <div class="stepwizard-step">
                <a href="#step-9" type="button" class="btn btn-default bg-secondary btn-circle" disabled="disabled" onclick="handleStepChange(9);">9</a>
                <p>Hypercholesterolemia</p>
            </div>

            <div class="stepwizard-step">
                <a href="#step-10" type="button" class="btn btn-default bg-secondary btn-circle" disabled="disabled" onclick="handleStepChange(10);">10</a>
                <p>Diabetes</p>
            </div>

            <div class="stepwizard-step">
                <a href="#step-11" type="button" class="btn btn-default bg-secondary btn-circle" disabled="disabled" onclick="handleStepChange(11);">11</a>
                <p class="word-break">Chronic Obstructive Pulmonary Disease</p>
            </div>

            <div class="stepwizard-step">
                <a href="#step-12" type="button" class="btn btn-default bg-secondary btn-circle" disabled="disabled" onclick="handleStepChange(12);">12</a>
                <p class="word-break">Chronic Kidney Disease</p>
            </div>
            
            <div class="stepwizard-step">
                <a href="#step-13" type="button" class="btn btn-default bg-secondary btn-circle" disabled="disabled" onclick="handleStepChange(13);">13</a>
                <p class="word-break">Hypertension</p>
            </div>
            
            <div class="stepwizard-step">
                <a href="#step-14" type="button" class="btn btn-default bg-secondary btn-circle" disabled="disabled" onclick="handleStepChange(14);">14</a>
                <p class="word-break">Obesity</p>
            </div>
            
            <div class="stepwizard-step">
                <a href="#step-15" type="button" class="btn btn-default bg-secondary btn-circle" disabled="disabled" onclick="handleStepChange(15);">15</a>
                <p class="word-break">Congestive Heart Failure</p>
            </div> --}}
        </div>
    </div>
</div>