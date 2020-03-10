import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AuthRoutingModule } from './auth-routing.module';
import { AuthComponent } from './auth.component';
import { SharedModule } from '../../modules/shared/shared.module';
import { AddFbAccountComponent } from './partials/add-fb-account/add-fb-account.component';
import { InvitationComponent } from './partials/invitation/invitation.component';
import { NotCorrectInvitationComponent } from './partials/not-correct-invitation/not-correct-invitation.component';
import { ShareCompanyComponent } from './partials/share-company/share-company.component';
import { NotProductComponent } from './partials/not-product/not-product.component';
import { LoginStandartComponent } from './partials/login-standart/login-standart.component';
import { ShareBotComponent } from './partials/share-bot/share-bot.component';
import { ShareAutoresponderComponent } from './partials/share-autoresponder/share-autoresponder.component';

@NgModule({
  declarations: [
    AuthComponent,
    AddFbAccountComponent,
    InvitationComponent,
    NotCorrectInvitationComponent,
    ShareCompanyComponent,
    NotProductComponent,
    LoginStandartComponent,
    ShareBotComponent,
    ShareAutoresponderComponent
  ],
  imports: [
    CommonModule,
    AuthRoutingModule,
    SharedModule
  ]
})
export class AuthModule { }
