@extends('layouts.crud')
@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Canned Reports</h1>
            <a href="{{ route('canned-reports.create') }}" class="btn btn-primary">New Canned Report</a>
        </div>

        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th><a href="{{ route('canned-reports.index', ['sort' => 'id', 'direction' => $sort === 'id' && $direction === 'asc' ? 'desc' : 'asc']) }}">Id</a></th>
                    <th><a href="{{ route('canned-reports.index', ['sort' => 'name', 'direction' => $sort === 'name' && $direction === 'asc' ? 'desc' : 'asc']) }}">Name</a></th>
                    <th><a href="{{ route('canned-reports.index', ['sort' => 'updated_at', 'direction' => $sort === 'updated_at' && $direction === 'asc' ? 'desc' : 'asc']) }}">Last Updated</a></th>
                    <th><a href="{{ route('canned-reports.index', ['sort' => 'updated_by', 'direction' => $sort === 'updated_by' && $direction === 'asc' ? 'desc' : 'asc']) }}">Updated By</a></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reports as $report)
                    <tr>
                        <td>{{ $report->id }}</td>
                        <td>{{ $report->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($report->updated_at)->format('n/j/y, g:i A') }}</td>
                        <td>{{ $report->updatedBy->name ?? 'Unknown' }}</td>
                        <td>
                            <a href="{{ route('canned-reports.show', $report) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('canned-reports.edit', $report) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('canned-reports.destroy', $report) }}" method="POST" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                            <a href="{{ url('example.com/'.$report->id) }}" class="btn btn-sm btn-info">Launch</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $reports->appends(request()->query())->links() }}
    </div>
@endsection
