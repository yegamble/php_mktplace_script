@extends('master.admin')

@section('admin-content')
    <div class="row mb-4">
        <div class="col">
            <h3>
                All Tickets
            </h3>
        </div>
    </div>

    <table class="table">
        <thead>
        <tr>
            <th>Title</th>
            <th>Opened by</th>
            <th>Time</th>
        </tr>
        </thead>
        <tbody>
        @foreach($tickets as $ticket)
            <tr>
                <td>
                    <a href="{{ route('admin.tickets.view', $ticket) }}" class="mt-1">{{ $ticket -> title }}</a>
                    @if($ticket -> solved)
                    <span class="badge badge-success">Solved</span>
                    @else
                        @if($ticket -> answered)
                            <span class="badge badge-warning">Answered</span>
                        @endif
                    @endif
                </td>
                <td>
                    <strong>{{ $ticket -> user -> username }}</strong>
                </td>
                <td>
                    <small>{{ $ticket -> time_passed }}</small>
                </td>

            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="text-center">
                {{ $tickets->links('includes.paginate') }}
            </div>
        </div>
    </div>



@stop