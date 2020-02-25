import React from 'react';
import ReactDOM from 'react-dom';
import axios from 'axios';
import Select from 'react-select';
import tabs from './constants/survey-tabs';

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

class Users extends React.Component {

    saveUser(user, e = null) {
        if (e)
            e.preventDefault();

        let endpoint = SURVEY_USERS_ENDPOINT;
        if (user.account_id)
            endpoint += '/' + user.account_id;

        axios.post(endpoint, user)
            .then(response => {
                $('#edit-user-modal').modal('hide');
                this.setState({users: response.data.data});
            })
            .catch(error => {
                console.log(error);
            });
    }

    deleteUser(user, e = null) {
        if (e)
            e.preventDefault();

        let endpoint = SURVEY_USERS_ENDPOINT + '/' + user.account_id;

        axios.delete(endpoint)
            .then(response => {
                $('#delete-user-modal').modal('hide');
                this.setState({users: response.data.data});
            })
            .catch(error => {
                console.log(error);
            });
    }

    deleteUserModal(user) {
        this.setState({user: user});
        $('#delete-user-modal').modal('show');
    }

    editUser(userId = 0) {
        if (userId) {
            axios.get(SURVEY_USERS_ENDPOINT + '/' + userId)
                .then(response => {
                    console.log(response);
                    this.setState({user: response.data.data});
                    // open modal
                    $('#edit-user-modal').modal('show');

                })
                .catch(error => {
                    console.log(error);
                });
        } else {
            var user = this.state.user;
            for (var p in user)
                if (user.hasOwnProperty(p))
                    user[p] = '';
            user.permissions = [];

            this.setState({user: user});

            // open modal
            $('#edit-user-modal').modal('show');
        }
    }

    getUsers() {
        axios.get(SURVEY_USERS_ENDPOINT)
            .then(response => {
                console.log(response);

                this.setState({users: response.data.data});
            })
            .catch(error => {
                console.log(error);
            });
    }

    hasPermission(user, name, value = null) {
        if (!user.permissions)
            return false;

        let permission = user.permissions.find((permission) => {
            return !!value
                ? (permission.name === name) && (permission.value == value)
                : permission.name === name;
        });

        return permission !== undefined;
    }

    handleChange(event) {
        const target = event.target;
        const value = target.type === 'checkbox' ? target.checked : target.value;
        const name = target.name;

        this.setState({
            user: {...this.state.user, [name]: value}
        });
    }

    handlePermissionChange(event) {
        const target = event.target;
        const value = target.checked;
        const name = target.name;

        let permissions = this.state.user.permissions.filter(p => p.name !== name);
        if (value) {
            permissions.push({
                name: name,
                value: SURVEY_ID,
            });
        }

        this.setState({
            user: {
                ...this.state.user,
                permissions: permissions
            }
        });
    }

    constructor() {
        super();

        this.state = {
            users: [],
            user: {},
        }

        this.getUsers();

        this.handleChange = this.handleChange.bind(this);
        this.handlePermissionChange = this.handlePermissionChange.bind(this);
    }

    render() {
        const { user } = this.state;

        const users = this.state.users.map((user) => {
            return (
                <tr key={user.account_id}>
                    <td className="edit-buttons">
                        <a href="#" title="Edit User" onClick={() => this.editUser(user.account_id)}><span className="glyphicon glyphicon-pencil"></span></a>
                        <a href="#" title="Delete User" onClick={() => this.deleteUserModal(user)}><span className="glyphicon glyphicon-trash"></span></a>
                    </td>
                    <td>{user.account_usn}</td>
                    <td>{user.account_email_address}</td>
                    <td>{user.account_last_name}, {user.account_first_name}</td>
                    <td>{user.last_login}</td>
                </tr>
            );
        });

        return (
            <div>
                <button className="btn btn-primary btn-sm" onClick={() => this.editUser()}><span className="glyphicon glyphicon-user"></span> Add User</button>

                <div className="well">
                    {users.length ? (
                        <table className="table table-striped" id="users">
                            <thead>
                                <tr>
                                    <td>Edit</td>
                                    <td>Username</td>
                                    <td>Email</td>
                                    <td>Name</td>
                                    <td>Last Login</td>
                                </tr>
                            </thead>
                            <tbody>
                                {users}
                            </tbody>
                        </table>
                    ) : (
                      <p>This survey has no users</p>
                    )}
                </div>

                <div className="modal fade" id="edit-user-modal" role="dialog">
                    <div className="modal-dialog">
                        <div className="modal-content">
                            <div className="modal-header">
                                <button type="button" className="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 className="modal-title"><span className="glyphicon glyphicon-user"></span> {this.state.user.account_id ? `Edit` : `Add`} User</h4>
                            </div>
                            <form method="post">
                                <div className="modal-body">
                                    <div id="personScroll">
                                        <input type="hidden" name="account_id" value={this.state.user.account_id} onChange={this.handleChange} />

                                        <div className="form-group">
                                            <label htmlFor="account_first_name">First Name</label>
                                            <input
                                                id="account-first-name"
                                                name="account_first_name"
                                                className="form-control txtbox"
                                                maxLength="255"
                                                placeholder="First Name"
                                                value={this.state.user.account_first_name}
                                                onChange={this.handleChange} />
                                        </div>

                                        <div className="form-group">
                                            <label htmlFor="account_last_name">Last Name</label>
                                            <input
                                                id="account-last-name"
                                                name="account_last_name"
                                                className="form-control txtbox"
                                                maxLength="255"
                                                placeholder="Last Name"
                                                value={this.state.user.account_last_name}
                                                onChange={this.handleChange} />
                                        </div>

                                        <div className="form-group">
                                            <label htmlFor="account_email_address">Email</label>
                                            <input
                                                id="account-email"
                                                name="account_email_address"
                                                className="form-control txtbox"
                                                maxLength="255"
                                                placeholder="Email"
                                                value={this.state.user.account_email_address}
                                                onChange={this.handleChange} />
                                        </div>

                                        <div className="form-group">
                                            <label htmlFor="account_usn">Username</label>
                                            <input
                                                id="account-username"
                                                name="account_usn"
                                                className="form-control txtbox"
                                                maxLength="255"
                                                placeholder="Username"
                                                value={this.state.user.account_usn}
                                                onChange={this.handleChange} />
                                        </div>

                                        <div className="form-group">
                                            <label htmlFor="account_pwd">Password</label>
                                            <input
                                                type="password"
                                                id="account-password"
                                                name="account_pwd"
                                                className="form-control txtbox"
                                                maxLength="255"
                                                placeholder="Password"
                                                value={this.state.user.account_pwd}
                                                onChange={this.handleChange} />
                                        </div>

                                        <div className="form-group permissions">
                                            <div className="form-check form-check-inline">

                                            {tabs.map((tab) => {
                                                return (
                                                        <label className="form-check-label" key={tab.name}>
                                                            <input
                                                                className="form-check-input"
                                                                type="checkbox"
                                                                name={tab.name}
                                                                id={tab.name}
                                                                // value={!!this.hasPermission(user, tab.name, SURVEY_ID)}
                                                                checked={!!this.hasPermission(user, tab.name, SURVEY_ID)}
                                                                onChange={this.handlePermissionChange}
                                                                /> {tab.label}
                                                        </label>
                                                );
                                            })}
                                            </div>

                                        </div>

                                    </div>

                                </div>
                                <div className="modal-footer">
                                    <button type="button" className="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                    <input
                                        type="submit"
                                        id="btnEditUser"
                                        className="btn btn-primary"
                                        name="btnEditUser"
                                        value="Save"
                                        disabled={!user.account_usn}
                                        onClick={(e) => this.saveUser(user, e)} />
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div className="modal fade" id="delete-user-modal" role="dialog">
                    <div className="modal-dialog">
                        <div className="modal-content">
                            <div className="modal-header">
                                <button type="button" className="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <h5 className="modal-title"><span className='glyphicon glyphicon-user'></span>&nbsp; Confirm Delete {user.account_first_name} {user.account_last_name}</h5>
                            </div>

                            <form method="post">
                                <div className="modal-body">
                                    <p className="surveyQuestion"><span style={{color: 'red'}}><strong>WARNING:</strong></span>
                                        This action will permanently delete user <strong>{user.account_first_name} {user.account_last_name}</strong> from the survey. Are you sure you wish to continue?</p>
                                </div>
                                <div className="modal-footer">
                                    <button type="button" className="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                    <input type="submit" className="btn btn-danger" value="Permanently Delete" onClick={(e) => this.deleteUser(user, e)} />
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        );
    }
}

class User extends React.Component {
    render() {
        return (
            <div>user</div>
        );
    }
}

ReactDOM.render(
    <Users />,
    document.getElementById('users')
);