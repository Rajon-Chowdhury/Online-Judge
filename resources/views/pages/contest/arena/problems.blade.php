@extends('layouts.contest_layout')
@section('title', 'Contests')
@section('content')
<div class="row">
    <div class="col-md-9">
        <div class="contestBox">
            <div class="contestBoxHeader">
                Problems
            </div>
            <div class="">
                <style type="text/css">
                    .labelAc{
		background-color: #5CB85C;
		color: #ffffff;
		padding: 3px 5px 3px 5px;
		border-radius: 4px;
		font-size: 12px!important;
	}
	.labelWa{
		background-color: #E35B5A;
		color: #ffffff;
		padding: 3px 5px 3px 5px;
		border-radius: 4px;
		font-size: 12px!important;
	}
	.labelNormal{
		background-color: #eeeeee;
		color: #000000;
		padding: 3px 5px 3px 5px;
		border-radius: 4px;
		font-size: 12px!important;
	}
    a:hover,a:focus{
        text-decoration: none;
    }
</style>

	@foreach($problems as $key => $problem)
@php
    $label = "labelNormal";
    $labelTitle = "Not Attempted";
    if(isset($problemsStat[$problem->id]['solved_by'][auth()->user()->id])){
        $label = $problemsStat[$problem->id]['solved_by'][auth()->user()->id] == 1 ? "labelAc" : "labelWa";
        $labelTitle = $label == "labelAc" ? "Solved" : "Attempted";
    }

@endphp

<a class="list-group-item problemListBox" href="{{route('contest.arena.problems.view',['contest_slug' => request()->contest_slug,'problem_no' => $key])}}" style="position: relative;">
    <div class="pull-right {{$label}}" title="{{$labelTitle}}" style="margin-top: 9px;">
        <i class="fa fa-user"></i>× {{isset($problemsStat[$problem->id]['solved']) ? $problemsStat[$problem->id]['solved'] : 0}} / {{isset($problemsStat[$problem->id]['attempted']) ? $problemsStat[$problem->id]['attempted'] : 0}} </div>
    <div class="pull-left problemNo">
                        {{$key}}
                    </div>
                    <h4 class="list-group-item-heading" style="margin: 5px 0 4px; line-height: 30px;font-weight: normal;">
                        {{$problem->name}}
                    </h4>
                </a>

	@endforeach

            </div>
        </div>
    </div>


    <div class="col-md-3">
        <div class="contestBox">
            <div class="contestBoxHeader">
                Resource
            </div>
            <div class="contestBoxBody">
                
            </div>
        </div>
        <div class="contestBox">
            <div class="contestBoxHeader">
                Announcement
            </div>
            <div class="contestBoxBody">
                @for($i=1; $i<=2; $i++)
                <div style="border: 1px solid #eeeeee; border-width: 0px 0px 1px 0px;padding: 10px 0px 10px 0px;">
                    <a href="">this is test this is test this is test this is test this is test this is test this is test this is test this is test</a><br/>
                    <small class="text-muted">56m ago</small>
                </div>
                @endfor
                <center>
                    <div style="padding-top: 10px;"><a href="">See more...</a></div>
                </center>
            </div>
        </div>
    </div>
</div>
@stop
