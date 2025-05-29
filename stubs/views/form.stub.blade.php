<form method="POST" action="{{ isset($post) ? route('posts.update', $post) : route('posts.store') }}">
    @csrf
    @if(isset($post)) @method('PUT') @endif

    {{ formFields }}

    <button type="submit" class="btn btn-primary">Submit</button>
</form>
