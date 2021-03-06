@extends('layouts.app')
@section('content')

<!-- Recipe image and summary-->
<div class="container-xl top">
    <h1>{{ $recipe->name }}</h1>
    <div class="row  ">
        <div class="col-md-7  ">
            <img src="{{ $recipe->image_path }}" class="img-fluid rounded-left " alt="...">
        </div>
        <div class="col-md-5 bg-pink m-left rounded-right ">

            <div class="row mt-2 ">
                <div class="col text-center">
                    <h6>Serves</h6>
                </div>
                <div class="col text-center border-start">
                    <h6>Prep Time</h6>
                </div>
                <div class="col text-center border-start">
                    <h6>Cook Time</h6>
                </div>
            </div>

            <div class="row ">
                <div class="col text-center ">
                    <h3>{{ $recipe->serves }}</h3>
                </div>
                <div class="col text-center border-start">
                    <h3>{{ $recipe->prepTime }} mins</h3>
                </div>
                <div class="col text-center border-start">
                    <h3>{{ $recipe->cookTime }} mins</h3>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <h1 class="text-center">
                        @for ($i = 0; $i < $recipe->rating; $i++) * @endfor
                            <span style="color:darkgray">
                                @for ($i = $recipe->rating; $i < 5; $i++) * @endfor </span> </h1> </div> </div> <div class="row">
                                    <div class="col text-center ">
                                        <h4>{{ $recipe->about }}</h4>
                                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <p class="text-end m-2">
                            <b> Author {{ $recipe->user->name }} </b>
                            <br>
                            <i> Created @php echo ($recipe->created_at)->format('m/d/Y') @endphp </i>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Ingredients and steps-->
<div class="container-xl ">
    <div class="row row-margin m-m-top">
        <div class="col-md-4 ">
            <div class="card bg-light-pink">
                <div class="card-body">
                    <h5 class="card-title">Ingredients</h5>
                    <h6 class="card-subtitle mb-2 text-muted">{!! nl2br(e($recipe->ingredients)) !!}</h6>
                </div>
            </div>
        </div>
        <div class="col-md-1">
        </div>
        <div class="col-md-7 m-m-top ">
            <div class="card bg-light-pink">
                <div class="card-body">
                    <h5 class="card-title">Steps</h5>
                    <h6 class="card-subtitle mb-2 text-muted">{!! nl2br(e($recipe->steps)) !!}</h6>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Comments-->
<div class="container mt-2">
    @auth
    @can('Recipe-in-my-cookbook', $recipe)
    <a class="mt-1 mx-auto btn btn-small btn-info" href="/recipes/{{ $recipe->id }}/comments/create">Comment </a>
    @endcan
    @endauth

    @php $right = false; @endphp
    @foreach ($recipe_comments as $recipe_comment)
    <div class="row mt-2">
        <div class="d-flex  @if ($right) justify-content-end @endif ">
            <div class=" card bg-dark ">
                <div class=" card-body">
                    <h4 class="card-title"> {{ $recipe_comment->comment }} </h4>
                    <div class="card-content row">
                        <div class="col-6">
                            @can('Delete-Comment', $recipe_comment)
                            <form action="/recipes/{{ $recipe->id }}/comments/{{ $recipe_comment->id }}" method="POST">
                                @method('DELETE')
                                @csrf
                                <button type="submit" title="delete" class="mt-1 mx-auto btn btn-small btn-danger">Delete </button>
                            </form>
                            @endcan
                        </div>
                        <h6 class="col-6 d-flex justify-content-end mt-2"> {{ $recipe_comment->user->name }} </h6>
                        @php $right = !$right; @endphp
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection