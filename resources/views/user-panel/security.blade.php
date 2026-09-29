@extends('user-panel.layout.app')

@section('content')


                    <div class="security-content text-center">

                        <h2>Security Settings</h2>

                        <div class="password-card mx-auto">

                            <h4>Change Account Password</h4>

                            <div class="mb-3">
                                <input type="password"
                                    class="form-control"
                                    placeholder="Current Password">
                            </div>

                            <div class="mb-4">
                                <input type="password"
                                    class="form-control"
                                    placeholder="New Password">
                            </div>

                            <button type="button" class="update-btn">
                                Update Password
                            </button>

                        </div>

                    </div>

                
@endsection
