@extends('layouts.app')

@section('title', 'Projects')

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h2 class="text-center mb-4">Projects</h2>
            <a href="{{ route('projects.add') }}" class="btn btn-success mb-3">Create New Project</a>

            <ul class="list-group">
                @foreach ($projects as $project)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <a href="{{ route('projects.show', $project->id) }}">{{ $project->name }}</a>
                        <form method="POST" action="{{ route('projects.destroy', $project->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endsection
