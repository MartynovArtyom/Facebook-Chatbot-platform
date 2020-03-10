import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { PageNotFoundComponent } from './view/page-not-found/page-not-found.component';

const appRoutes: Routes = [
  { path: '', loadChildren: './view/auth/auth.module#AuthModule' },
  { path: '', loadChildren: './view/admin/admin.module#AdminModule' },
  { path: '', loadChildren: './view/user/user.module#UserModule' },
  { path: '**', component: PageNotFoundComponent }
];

@NgModule({
  imports: [
    RouterModule.forRoot(appRoutes)
  ],
  exports: [
    RouterModule
  ]
})
export class AppRoutingModule { }
