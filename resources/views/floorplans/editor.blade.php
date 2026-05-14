@extends('layouts.app')

@section('title', 'محرر المخطط')

@section('body')
    <div
        id="floorplan-editor"
        data-config='@json($editorConfig)'
    ></div>

    @vite('resources/js/editor/app.js')
@endsection
