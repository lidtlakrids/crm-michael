/**
 * Created by dib on 03-Apr-16.
 * custom rules for each role and model.....
 * basically who can see what
 */
function checkOwnership(item){

    if(isInArray('Administrator',roles) || isInArray('Developer',roles)){
        return '';
    }
    var userid = getUserId();

    // Find and remove the user role from the array
    var i = roles.indexOf("User");
    if(i != -1) {
        roles.splice(i, 1);
    }
    switch (item) {
        case "Invoices":
            switch (roles[0]){
                case "Client Manager":
                    return " and ClientAlias/Client/ClientManager_Id eq '"+userid+"'";
                    break;
                case "Sales":
                default:
                    return " and User_Id eq '"+userid+"'";
                break;
            }
        break;
        case "Contract":
            switch (roles[0]){
                case "Client Manager":
                    return " and (ClientAlias/Client/ClientManager_Id eq null or ClientAlias/Client/ClientManager_Id eq '"+userid+"')";
                    break;
                case "Adwords":
                case "SEO":
                    return " and Manager_Id eq '"+userid+"'";
                    break;
                case "Sales":
                default:
                    return " and User_Id eq '"+userid+"'";
                    break;
            }
            break;

        case "Orders":
        case "Clients":
            switch (roles[0]){
                case "Client Manager":
                    return " and (ClientAlias/Client/ClientManager_Id eq null or ClientAlias/Client/ClientManager_Id eq '"+userid+"')";
                    break;
                case "Sales":
                default:
                    return " and User_Id eq '"+userid+"'";
                    break;
            }
            break;

        case "Leads":
            return " and (User_Id eq '"+userid+"' or Booker_Id eq '"+userid+"' or UserSource_Id eq '"+userid+"')";
            break;
        default :
            return " and User_Id eq '"+userid+"'";
            break;
    }
}

/**
 * checks if a user is in certain role , or admin/dev
 */
function inRole(role) {
    return isInArray('Administrator',roles) || isInArray('Developer',roles)? true : isInArray(role,roles);
}

function inRoleNeutral(role) {
    return isInArray(role,roles);
}

/**
 * is it admin or dev
 * @returns {*}
 */
function isAdmin() {
    return isInArray('Developer', roles);
}