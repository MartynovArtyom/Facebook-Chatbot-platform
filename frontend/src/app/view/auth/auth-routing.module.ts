import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { AuthComponent } from './auth.component';
import { AddFbAccountComponent } from './partials/add-fb-account/add-fb-account.component';
import { CheckAddFbAccountGuard } from '../../guards/check-add-fb-account.guard';
import { InvitationComponent } from './partials/invitation/invitation.component';
import { NotCorrectInvitationComponent } from './partials/not-correct-invitation/not-correct-invitation.component';
import { UserInputDateComponent } from '../../modules/shared/user-input-date/user-input-date.component';
import { UserInputDatetimeComponent } from '../../modules/shared/user-input-datetime/user-input-datetime.component';
import { CheckAuthLoginGuard } from '../../guards/check-auth-login.guard';
import { ShareCompanyComponent } from './partials/share-company/share-company.component';
import { NotProductComponent } from './partials/not-product/not-product.component';
import { LoginStandartComponent } from './partials/login-standart/login-standart.component';
import { ShareBotComponent } from './partials/share-bot/share-bot.component';
import { CheckProductGuard } from '../../guards/check-product.guard';
import { ShareAutoresponderComponent } from './partials/share-autoresponder/share-autoresponder.component';

const routes: Routes = [
  { path: '', redirectTo: 'login', pathMatch: 'full' },
  { path: 'login', canActivate: [ CheckAuthLoginGuard ], component: AuthComponent },
  { path: 'x', canActivate: [ CheckAuthLoginGuard ], component: LoginStandartComponent },
  { path: 'add-fb-account', canActivate: [ CheckAddFbAccountGuard, CheckProductGuard ], component: AddFbAccountComponent },
  { path: 'invitation/:id', canActivate: [ CheckProductGuard ], component: InvitationComponent },
  { path: 'picker_date', component: UserInputDateComponent },
  { path: 'picker_date_time', component: UserInputDatetimeComponent },
  { path: 'share-kampagnen', canActivate: [ CheckProductGuard ], component: ShareCompanyComponent },
  { path: 'share-kampagnen/:id', canActivate: [ CheckProductGuard ], component: ShareCompanyComponent },
  { path: 'share-autoresponder', canActivate: [ CheckProductGuard ], component: ShareAutoresponderComponent },
  { path: 'share-autoresponder/:id', canActivate: [ CheckProductGuard ], component: ShareAutoresponderComponent },
  { path: 'shareBot', canActivate: [ CheckProductGuard ], component: ShareBotComponent },
  { path: 'shareBot/:id', canActivate: [ CheckProductGuard ], component: ShareBotComponent },
  { path: 'invitation-not-correct', canActivate: [ CheckProductGuard ], component: NotCorrectInvitationComponent },
  { path: 'not-product', component: NotProductComponent }
];

@NgModule({
  imports: [
    RouterModule.forChild(routes)
  ],
  exports: [
    RouterModule
  ]
})
export class AuthRoutingModule { }
