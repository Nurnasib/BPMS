@extends('layouts.app')

@section('title', 'Create Project')

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h2 class="text-center mb-4">Create New Project</h2>
            <form method="POST" action="{{ route('projects.store') }}">
                @csrf
                <div class="mb-3">
                    <label for="title" class="form-label">Title:</label>
                    <input type="text" name="title" id="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="team_leader" class="form-label">Team Leader:</label>
                    <select name="team_leader" id="team_leader" class="form-select" required>
                        @foreach ($teamLeaders as $leader)
                            <option value="{{ $leader->id }}">{{ $leader->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100">Create Project</button>
            </form>
        </div>
    </div>
@endsection
