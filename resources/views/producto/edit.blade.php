@extends('layouts.app')

@section('content')
<div class="card">

    <h5 class="card-header info-color white-text text-center py-4">
        <strong>Modificar Producto</strong>
    </h5>

    <!--Card content-->
    <div class="card-body px-lg-5 pt-0">
        <a href="{{ url('/producto') }}" title="Back"><button class="btn btn-warning btn-sm"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back</button></a>

        <p><small class="red-text">* Obligatorio</small></p>
        
        <form method="POST" class="text-center" action="{{ url('/producto/' . $producto->id) }}" accept-charset="UTF-8" style="color: #757575;" enctype="multipart/form-data">
            {{ method_field('PATCH') }}
            @include ('producto.form', ['submitButtonText' => 'MODIFICAR'])
        </form>

    </div>

</div>
@endsection
@push('scripts')
<script type="text/javascript">
    $(document).ready(function () {
        $('#cmi-producto').addClass('current-menu-item');
        $('#a-producto').addClass('active');
    });
</script>
@endpush