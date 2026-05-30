@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('App Version Management') }}</div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('app-version.update') }}">
                            @csrf
                            @method('PUT')

                            <div class="form-group">
                                <label for="latest_version">{{ __('Latest Version') }}</label>
                                <input id="latest_version" type="text"
                                       class="form-control @error('latest_version') is-invalid @enderror"
                                       name="latest_version"
                                       value="{{ old('latest_version', $appVersion->latest_version) }}"
                                       required>
                                @error('latest_version')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="minimum_supported_version">{{ __('Minimum Supported Version') }}</label>
                                <input id="minimum_supported_version" type="text"
                                       class="form-control @error('minimum_supported_version') is-invalid @enderror"
                                       name="minimum_supported_version"
                                       value="{{ old('minimum_supported_version', $appVersion->minimum_supported_version) }}"
                                       required>
                                @error('minimum_supported_version')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="form-group form-check">
                                <input id="force_update" type="checkbox" class="form-check-input"
                                       name="force_update" value="1"
                                       {{ old('force_update', $appVersion->force_update) ? 'checked' : '' }}>
                                <label class="form-check-label" for="force_update">{{ __('Force Update') }}</label>
                            </div>

                            <div class="form-group">
                                <label for="update_message">{{ __('Update Message') }}</label>
                                <textarea id="update_message"
                                          class="form-control @error('update_message') is-invalid @enderror"
                                          name="update_message" rows="4">{{ old('update_message', $appVersion->update_message) }}</textarea>
                                @error('update_message')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="play_store_url">{{ __('Play Store URL') }}</label>
                                <input id="play_store_url" type="url"
                                       class="form-control @error('play_store_url') is-invalid @enderror"
                                       name="play_store_url"
                                       value="{{ old('play_store_url', $appVersion->play_store_url) }}">
                                @error('play_store_url')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
