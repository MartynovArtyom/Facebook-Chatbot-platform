import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { AdminRoutingModule } from './admin-routing.module';
import { AdminDashboardComponent } from './partials/admin-dashboard/admin-dashboard.component';
import { AdminComponent } from './admin.component';
import { AdminSidebarComponent } from './partials/admin-sidebar/admin-sidebar.component';
import { AdminPagesComponent } from './partials/admin-pages/admin-pages.component';
import { AdminUsersComponent } from './partials/admin-users/admin-users.component';
import { SharedModule } from '../../modules/shared/shared.module';
import { BrowserModule } from '@angular/platform-browser';
import { AdminUserPageComponent } from './partials/admin-users/admin-user-page/admin-user-page.component';
import { AdminPageIdComponent } from './partials/admin-pages/admin-page-id/admin-page-id.component';
import { AdminProductsComponent } from './partials/admin-products/admin-products.component';

@NgModule({
  declarations: [
    AdminDashboardComponent,
    AdminComponent,
    AdminSidebarComponent,
    AdminPagesComponent,
    AdminUsersComponent,
    AdminUserPageComponent,
    AdminPageIdComponent,
    AdminProductsComponent
  ],
  imports: [
    CommonModule,
    BrowserModule,
    AdminRoutingModule,
    SharedModule
  ]
})
export class AdminModule { }
