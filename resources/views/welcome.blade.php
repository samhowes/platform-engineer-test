@extends('layouts.app')

@section('title', 'Find Movies')

@section('scripts')
<script>
    let set = false
    // kindof hacky for simplicity: get the user's timezone so the server can render datetimes in
    // the user's local timezone
    document.onreadystatechange = (e) => {
        if (set) return
        const f = document.getElementById('queryForm');
        console.log(f)
        const tz = document.createElement('input')
        tz.setAttribute('type', 'hidden')
        tz.setAttribute('name', 'tz')
        tz.setAttribute('value', Intl.DateTimeFormat().resolvedOptions().timeZone)
        console.log(tz)
        f.appendChild(tz)
        set = true
    }

</script>
@endsection

@section('content')
    <div class="step">
        Find movies/shows made in ABQ
    </div>
    <form id="queryForm" method="POST" action="/show">
        @csrf
        <div class="input-group">
            <label for="start_date">Start Date</label>
            <input name="start_date" type="date" class="@error('start_date') is-invalid @enderror">
            @error('start_date')
                <div class="alert">{{ $message }}</div>
            @enderror
        </div>
        <div class="input-group">
            <label for="end_date">End Date</label>
            <input name="end_date" type="date" class="@error('end_date') is-invalid @enderror">
            @error('end_date')
                <div class="alert">{{ $message }}</div>
            @enderror
        </div>
        <input type="submit" value="Find Movies" class="button"/>
    </form>
@endsection
