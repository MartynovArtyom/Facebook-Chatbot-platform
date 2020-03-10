import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { AdminComponent } from './admin.component';
import { AdminDashboardComponent} from './partials/admin-dashboard/admin-dashboard.component';
import { AdminUsersComponent } from './partials/admin-users/admin-users.component';
import { AdminPagesComponent } from './partials/admin-pages/admin-pages.component';
import { AdminUserPageComponent } from './partials/admin-users/admin-user-page/admin-user-page.component';
import { AdminPageIdComponent } from './partials/admin-pages/admin-page-id/admin-page-id.component';
import { CheckAdminRoleGuard } from '../../guards/check-admin-role.guard';
import {AdminProductsComponent} from './partials/admin-products/admin-products.component';


const adminRouters: Routes = [
  { path: '', redirectTo: 'admin', pathMatch: 'full' },
  { path: 'admin', component: AdminComponent, children: [
    { path: '', redirectTo: 'dashboard', pathMatch: 'full' },
    { path: 'dashboard', canActivate: [ CheckAdminRoleGuard ], component: AdminDashboardComponent },
    { path: 'users', canActivate: [ CheckAdminRoleGuard ], component: AdminUsersComponent },
    { path: 'users/:id', canActivate: [ CheckAdminRoleGuard ], component: AdminUserPageComponent },
    { path: 'pages', canActivate: [ CheckAdminRoleGuard ], component: AdminPagesComponent },
    { path: 'pages/:id', canActivate: [ CheckAdminRoleGuard ], component: AdminPageIdComponent },
    { path: 'products', canActivate: [ CheckAdminRoleGuard ], component: AdminProductsComponent },
  ]}
];

@NgModule({
  imports: [
    RouterModule.forChild(adminRouters)
  ],
  exports: [
    RouterModule
  ]
})
export class AdminRoutingModule { }
