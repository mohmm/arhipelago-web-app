import Api from './Api'
import Auth from './Auth'
import LegalPageController from './LegalPageController'
import DashboardController from './DashboardController'
import Admin from './Admin'
import Settings from './Settings'

const Controllers = {
    Api: Object.assign(Api, Api),
    Auth: Object.assign(Auth, Auth),
    LegalPageController: Object.assign(LegalPageController, LegalPageController),
    DashboardController: Object.assign(DashboardController, DashboardController),
    Admin: Object.assign(Admin, Admin),
    Settings: Object.assign(Settings, Settings),
}

export default Controllers