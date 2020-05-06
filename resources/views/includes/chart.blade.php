<chart-component 
    :time_marks = "{{$events->pluck('formatedDate')}}" 
    :object_t   = "{{$events->pluck('object_t')}}"
    :outside_t = "{{$events->pluck('outside_t')}}"
    :direct_t = "{{$events->pluck('direct_t')}}"
    :back_t = "{{$events->pluck('back_t')}}"    
></chart-component>