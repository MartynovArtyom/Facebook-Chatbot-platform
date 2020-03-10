import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { AppComponent } from './app.component';
import { AppRoutingModule } from './app-routing.module';
import { AuthServiceConfig, FacebookLoginProvider, SocialLoginModule } from 'angular4-social-login';
import { SharedModule } from './modules/shared/shared.module';
import { HttpClientModule } from '@angular/common/http';
import { UserModule } from './view/user/user.module';
import { AdminModule } from './view/admin/admin.module';
import { AuthModule } from './view/auth/auth.module';
import { CommonModule } from '@angular/common';
import { FbService } from './services/fb.service';
import { SettingsService } from './services/settings.service';
import { NgSelectModule } from '@ng-select/ng-select';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { ToastrModule } from 'ngx-toastr';
import { CalendarDatePipe } from './pipes/calendar-date.pipe';
import { environment } from '../environments/environment';
import { CookieService } from 'ngx-cookie-service';

const config = new AuthServiceConfig([
  {
    id: FacebookLoginProvider.PROVIDER_ID,
    provider: new FacebookLoginProvider(String(environment.apiId),
      'email, manage_pages, ' +
      'pages_show_list, read_page_mailboxes, ' +
      'business_management, pages_messaging, ' +
      'pages_messaging_subscriptions, public_profile')
  }
]);

export function provideConfig() {
  return config;
}

@NgModule({
  declarations: [
    AppComponent,
    CalendarDatePipe
  ],
  imports: [
    CommonModule,
    BrowserModule,
    AuthModule,
    AdminModule,
    UserModule,
    HttpClientModule,
    AppRoutingModule,
    SharedModule,
    SocialLoginModule,
    NgSelectModule,
    BrowserAnimationsModule,
    ToastrModule.forRoot()
  ],
  providers: [
    {
      provide: AuthServiceConfig,
      useFactory: provideConfig
    },
    SettingsService,
    FbService,
    CookieService
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }
