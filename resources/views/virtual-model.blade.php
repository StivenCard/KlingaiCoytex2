@extends('layouts.app')

@section('content')
<div class="main-container">
    <div class="left-panel">
        <div class="virtual-model-form">
            <h3 style="color: var(--text-primary); margin-bottom: 20px;">
                <i class="fas fa-user-plus"></i> Generate Virtual Model
            </h3>

            <form id="virtualModelForm">
                @csrf
                <div class="form-group">
                    <label class="form-label">Prompt Description</label>
                    <textarea name="prompt" class="form-control form-textarea" placeholder="Describe the virtual model you want to generate..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Age Group</label>
                    <select name="age_group" class="form-select">
                        <option value="youth">Youth</option>
                        <option value="children">Children</option>
                        <option value="elderly">Elderly</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Skin Tone</label>
                    <select name="skin_tone" class="form-select">
                        <option value="light">Light</option>
                        <option value="medium">Medium</option>
                        <option value="dark">Dark</option>
                        <option value="olive">Olive</option>
                    </select>
                </div>

                <button type="button" class="generate-btn" id="generateVirtualModelBtn">
                    <i class="fas fa-magic"></i>
                    Generate Virtual Model
                </button>
            </form>
        </div>
    </div>

    <div class="right-panel">
        <div class="results-header">
            <i class="fas fa-user"></i>
            <span>Virtual Models</span>
        </div>

        <div class="results-content">
            <div class="result-section">
                <div id="virtualModelResultsContainer">
                    <div id="emptyState" class="text-center" style="padding: 60px 20px; color: var(--text-secondary);">
                        <i class="fas fa-user" style="font-size: 48px; margin-bottom: 16px; opacity: 0.3;"></i>
                        <p>No virtual models yet</p>
                        <small>Complete the form to generate your first virtual model</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#generateVirtualModelBtn').click(function() {
        alert('Virtual Model functionality coming soon!');
    });
});
</script>
@endpush
